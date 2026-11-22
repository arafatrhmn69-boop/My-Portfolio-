<?php

declare(strict_types=1);

/**
 * Secure image upload handling.
 *
 * Defence layers:
 *  1. PHP upload error code is checked first.
 *  2. is_uploaded_file() confirms this really came through an HTTP upload.
 *  3. Size limit is enforced in bytes and again after inspection.
 *  4. The real MIME type is read with finfo — never trusted from the client.
 *  5. getimagesize() confirms a decodable image and sane dimensions.
 *  6. The extension comes from the MIME allow-list, never the filename.
 *  7. A random 32-hex-character name is generated.
 *  8. Files land in /uploads/trips, which is configured never to execute PHP.
 *
 * Deletion is restricted to files directly inside the upload directory, so
 * seeded photographs in /images can never be removed through the panel.
 */
final class ImageUploader
{
    public function __construct(
        private string $directory = UPLOAD_PATH,
        private string $urlPrefix = UPLOAD_URL_PREFIX
    ) {
    }

    /**
     * Normalise a $_FILES entry into a list of single-file arrays.
     * Works for both `image` and `photos[]` style inputs.
     *
     * @param array<string, mixed>|null $files
     * @return array<int, array{name: string, type: string, tmp_name: string, error: int, size: int}>
     */
    public static function normaliseFiles(?array $files): array
    {
        if ($files === null || !isset($files['name'])) {
            return [];
        }

        if (!is_array($files['name'])) {
            return [[
                'name'     => (string) ($files['name'] ?? ''),
                'type'     => (string) ($files['type'] ?? ''),
                'tmp_name' => (string) ($files['tmp_name'] ?? ''),
                'error'    => (int) ($files['error'] ?? UPLOAD_ERR_NO_FILE),
                'size'     => (int) ($files['size'] ?? 0),
            ]];
        }

        $normalised = [];

        foreach (array_keys($files['name']) as $index) {
            $normalised[] = [
                'name'     => (string) ($files['name'][$index] ?? ''),
                'type'     => (string) ($files['type'][$index] ?? ''),
                'tmp_name' => (string) ($files['tmp_name'][$index] ?? ''),
                'error'    => (int) ($files['error'][$index] ?? UPLOAD_ERR_NO_FILE),
                'size'     => (int) ($files['size'][$index] ?? 0),
            ];
        }

        return $normalised;
    }

    /**
     * Move an uploaded image into the upload directory.
     *
     * @param array<string, mixed>|null $file A single entry from $_FILES.
     * @return string|null Stored relative path (uploads/trips/xxx.jpg) or null.
     */
    public function store(?array $file, ?string &$error = null): ?string
    {
        $error = null;

        if ($file === null || !isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $code = (int) $file['error'];

        if ($code !== UPLOAD_ERR_OK) {
            $error = $this->uploadErrorMessage($code);

            return null;
        }

        $tmpPath = $file['tmp_name'] ?? '';

        if (!is_string($tmpPath) || $tmpPath === '' || !is_uploaded_file($tmpPath)) {
            $error = 'The upload could not be verified. Please choose the file again.';

            return null;
        }

        $declaredSize = (int) ($file['size'] ?? 0);

        if ($declaredSize > MAX_UPLOAD_BYTES) {
            $error = 'The image is larger than ' . $this->readableSize(MAX_UPLOAD_BYTES) . '.';

            return null;
        }

        try {
            $info = $this->inspect($tmpPath);
        } catch (RuntimeException $exception) {
            $error = $exception->getMessage();

            return null;
        }

        if (!is_dir($this->directory) && !mkdir($this->directory, 0775, true) && !is_dir($this->directory)) {
            $error = 'The upload directory is not writable. Check the permissions on /uploads.';

            return null;
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $info['extension'];
        $target = rtrim($this->directory, '/') . '/' . $filename;

        if (!move_uploaded_file($tmpPath, $target)) {
            $error = 'The image could not be saved. Check the permissions on /uploads.';

            return null;
        }

        @chmod($target, 0644);

        return trim($this->urlPrefix, '/') . '/' . $filename;
    }

    /**
     * Validate a file on disk without moving it. Also used by the test suite.
     *
     * @return array{mime: string, extension: string, width: int, height: int, size: int}
     * @throws RuntimeException with a user-safe message.
     */
    public function inspect(string $path): array
    {
        if (!is_file($path)) {
            throw new RuntimeException('The uploaded file could not be found.');
        }

        $size = (int) filesize($path);

        if ($size <= 0) {
            throw new RuntimeException('The uploaded file is empty.');
        }

        if ($size > MAX_UPLOAD_BYTES) {
            throw new RuntimeException('The image is larger than ' . $this->readableSize(MAX_UPLOAD_BYTES) . '.');
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($path);

        if (!is_string($mime) || !isset(ALLOWED_IMAGE_MIMES[$mime])) {
            throw new RuntimeException('Only JPG, PNG and WebP images are allowed.');
        }

        $dimensions = @getimagesize($path);

        if ($dimensions === false || $dimensions[0] < 10 || $dimensions[1] < 10) {
            throw new RuntimeException('That file is not a valid image.');
        }

        [$width, $height] = [(int) $dimensions[0], (int) $dimensions[1]];

        // Reject decompression bombs / absurd camera exports.
        if ($width > 12000 || $height > 12000 || ($width * $height) > 60000000) {
            throw new RuntimeException('The image dimensions are too large (maximum 60 megapixels).');
        }

        return [
            'mime'      => $mime,
            'extension' => ALLOWED_IMAGE_MIMES[$mime],
            'width'     => $width,
            'height'    => $height,
            'size'      => $size,
        ];
    }

    /**
     * Delete a file that belongs to this uploader. Paths outside the upload
     * directory (for example images/cox-bazar/thumbnail.jpg) are ignored.
     */
    public function delete(?string $storedPath): bool
    {
        $storedPath = trim((string) $storedPath);
        $prefix = trim($this->urlPrefix, '/') . '/';

        if ($storedPath === '' || !str_starts_with($storedPath, $prefix)) {
            return false;
        }

        $relative = substr($storedPath, strlen($prefix));

        if ($relative === '' || str_contains($relative, '..') || str_contains($relative, '/')) {
            return false;
        }

        $base = realpath($this->directory);

        if ($base === false) {
            return false;
        }

        $target = realpath($base . DIRECTORY_SEPARATOR . $relative);

        if ($target === false || !str_starts_with($target, $base . DIRECTORY_SEPARATOR)) {
            return false;
        }

        return @unlink($target);
    }

    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The image is larger than ' . $this->readableSize(MAX_UPLOAD_BYTES) . '.',
            UPLOAD_ERR_PARTIAL    => 'The upload was interrupted. Please try again.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'The server has no temporary upload directory.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write the uploaded file.',
            UPLOAD_ERR_EXTENSION  => 'A server extension blocked the upload.',
            default               => 'The upload failed. Please try again.',
        };
    }

    private function readableSize(int $bytes): string
    {
        return (int) round($bytes / 1024 / 1024) . ' MB';
    }
}
