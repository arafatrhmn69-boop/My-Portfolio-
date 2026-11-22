<?php

declare(strict_types=1);

/**
 * Delete a gallery photograph. POST + CSRF only.
 * The database row and the uploaded image file are both removed.
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

Auth::requireLogin();

if (!is_post()) {
    if (!headers_sent()) {
        header('Allow: POST');
    }
    abort_request(405, 'Method not allowed', 'Photographs can only be deleted from the gallery manager.');
}

Csrf::requireValid();

$pdo = Database::connection();
$galleryModel = new GalleryPhoto($pdo);
$uploader = new ImageUploader(GALLERY_UPLOAD_PATH, GALLERY_URL_PREFIX);

$photoId = (int) ($_POST['id'] ?? 0);
$photo = $photoId > 0 ? $galleryModel->find($photoId) : null;
$returnAlbum = GalleryPhoto::normaliseAlbum(input_string($_POST, 'album'));

if ($photo === null) {
    Flash::add('warning', 'That photograph no longer exists.');
} else {
    $galleryModel->delete($photoId);

    // Only files inside /uploads/gallery are removed; seeded photographs in
    // /images are intentionally protected by ImageUploader::delete().
    $wasUploaded = str_starts_with(
        trim((string) $photo['image_path']),
        trim(GALLERY_URL_PREFIX, '/') . '/'
    );
    $fileRemoved = $uploader->delete($photo['image_path']);

    Flash::add('success', 'The photograph was deleted from the gallery.');

    if ($wasUploaded && !$fileRemoved) {
        Flash::add(
            'warning',
            'The database entry was removed, but the uploaded file could not be deleted. Check permissions on /uploads/gallery.'
        );
    }
}

$target = admin_url('gallery.php');

if ($returnAlbum !== '') {
    $target .= '?album=' . rawurlencode($returnAlbum);
}

redirect($target);
