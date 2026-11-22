<?php

declare(strict_types=1);

/**
 * Gallery / Album manager.
 *
 * One page handles:
 *  - batch upload of 1–10 photographs into a named album
 *  - searchable, album-filtered, paginated photo grid
 *  - per-photo delete (delegated to gallery-delete.php)
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

Auth::requireLogin();

$pdo = Database::connection();
$galleryModel = new GalleryPhoto($pdo);
$uploader = new ImageUploader(GALLERY_UPLOAD_PATH, GALLERY_URL_PREFIX);

$albumFilter = GalleryPhoto::normaliseAlbum(input_string($_GET, 'album'));
$search = mb_substr(input_string($_GET, 'search'), 0, 120, 'UTF-8');
$page = max(1, (int) ($_GET['page'] ?? 1));

$albums = $galleryModel->albums();

$errors = [];
$form = [
    'album_name' => $albumFilter,
    'caption'    => '',
];

if (is_post()) {
    Csrf::requireValid();

    $form['album_name'] = trim(preg_replace('/\s+/u', ' ', input_string($_POST, 'album_name')) ?? '');
    $form['caption'] = trim(input_string($_POST, 'caption'));

    $validator = (new Validator($form))
        ->required('album_name', 'Album name')
        ->maxLength('album_name', GALLERY_MAX_ALBUM_NAME, 'Album name')
        ->maxLength('caption', GALLERY_MAX_CAPTION, 'Caption');

    $errors = $validator->errors();

    $files = ImageUploader::normaliseFiles($_FILES['photos'] ?? null);
    $files = array_values(array_filter(
        $files,
        static fn (array $file): bool => (int) $file['error'] !== UPLOAD_ERR_NO_FILE
    ));

    if ($files === []) {
        $errors['photos'] = 'Choose at least one photograph to upload.';
    } elseif (count($files) > GALLERY_MAX_FILES) {
        $errors['photos'] = 'Please upload at most ' . GALLERY_MAX_FILES . ' photographs at a time.';
    }

    if ($errors === []) {
        $albumName = GalleryPhoto::normaliseAlbum($form['album_name']);

        // Reuse the existing spelling when the admin types a case variant.
        foreach ($albums as $existingAlbum) {
            if (strcasecmp($existingAlbum['album_name'], $albumName) === 0) {
                $albumName = $existingAlbum['album_name'];

                break;
            }
        }

        $caption = $form['caption'];
        $storedPaths = [];
        $uploadProblems = [];

        foreach ($files as $file) {
            $uploadError = null;
            $storedPath = $uploader->store($file, $uploadError);

            if ($storedPath === null) {
                $name = trim((string) $file['name']);
                $uploadProblems[] = ($name !== '' ? $name . ': ' : '') . ($uploadError ?? 'The upload failed.');

                continue;
            }

            $storedPaths[] = $storedPath;
        }

        if ($storedPaths === []) {
            $errors['photos'] = implode(' ', array_slice(array_unique($uploadProblems), 0, 3));
        } else {
            $pdo->beginTransaction();

            try {
                foreach ($storedPaths as $storedPath) {
                    $galleryModel->create($albumName, $caption, $storedPath);
                }

                $pdo->commit();
            } catch (Throwable $error) {
                $pdo->rollBack();

                foreach ($storedPaths as $storedPath) {
                    $uploader->delete($storedPath);
                }

                throw $error;
            }

            $uploaded = count($storedPaths);
            Flash::add(
                'success',
                $uploaded . ' photograph' . ($uploaded === 1 ? '' : 's')
                . ' added to “' . $albumName . '”.'
            );

            if ($uploadProblems !== []) {
                $skipped = count($uploadProblems);
                $details = implode(' ', array_slice(array_unique($uploadProblems), 0, 3));
                Flash::add('warning', $skipped . ' file' . ($skipped === 1 ? ' was' : 's were') . ' skipped: ' . $details);
            }

            redirect(admin_url('gallery.php?album=' . rawurlencode($albumName)));
        }
    }
}

$result = $galleryModel->paginate($page, GALLERY_PER_PAGE, $albumFilter, $search);
$totalPhotos = $galleryModel->count();
$albumCount = count($albums);

$pageTitle = 'Gallery / Albums';
$pageHeading = 'Gallery / Albums';
$pageEyebrow = 'Media';
$pageSubtitle = 'Upload photographs in batches, group them into albums and remove anything that no longer belongs on the public gallery.';
$activePage = 'gallery';

$pageActions = '<a class="btn-line" href="' . e(url('gallery.php')) . '" target="_blank" rel="noopener">'
    . '<i class="bi bi-eye" aria-hidden="true"></i> View public gallery</a>'
    . '<a class="btn-gold" href="#gallery-upload">'
    . '<i class="bi bi-cloud-arrow-up" aria-hidden="true"></i> Upload photos</a>';

require __DIR__ . '/partials/header.php';
?>

<section class="admin-card" id="gallery-upload" aria-labelledby="gallery-upload-title">
    <div class="admin-card__head">
        <h2 class="admin-card__title" id="gallery-upload-title">Upload <span>photographs</span></h2>
        <span class="badge-soft badge-soft--gold">JPG · PNG · WebP · max 5 MB each</span>
    </div>

    <form method="post" action="<?= e(admin_url('gallery.php')) ?>" enctype="multipart/form-data"
        data-submit-once>
        <?= Csrf::field() ?>

        <div class="admin-card__body">
            <?php if ($errors !== []): ?>
                <div class="admin-alert admin-alert--error" role="alert" style="margin-bottom:18px">
                    <i class="bi bi-exclamation-octagon" aria-hidden="true"></i>
                    <p>
                        <?= e(count($errors) === 1 ? reset($errors) : 'Please review the highlighted fields and try again.') ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-field">
                    <label class="form-label-admin" for="album_name">
                        Album name <span class="req" aria-hidden="true">*</span>
                    </label>
                    <input class="form-control-admin<?= isset($errors['album_name']) ? ' is-invalid-admin' : '' ?>"
                        type="text" id="album_name" name="album_name" value="<?= e($form['album_name']) ?>"
                        maxlength="<?= e((string) GALLERY_MAX_ALBUM_NAME) ?>" list="gallery-albums" required
                        placeholder="Cox's Bazar"
                        <?= isset($errors['album_name']) ? 'aria-invalid="true" aria-describedby="error-album_name"' : '' ?>>
                    <datalist id="gallery-albums">
                        <?php foreach ($albums as $album): ?>
                            <option value="<?= e($album['album_name']) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <?php if (isset($errors['album_name'])): ?>
                        <span class="form-error" id="error-album_name"><?= e($errors['album_name']) ?></span>
                    <?php else: ?>
                        <p class="form-hint">Reuse an existing name to add to that album, or type a new one.</p>
                    <?php endif; ?>
                </div>

                <div class="form-field">
                    <label class="form-label-admin" for="caption">Caption <span
                            class="text-muted-admin">(optional)</span></label>
                    <input class="form-control-admin<?= isset($errors['caption']) ? ' is-invalid-admin' : '' ?>"
                        type="text" id="caption" name="caption" value="<?= e($form['caption']) ?>"
                        maxlength="<?= e((string) GALLERY_MAX_CAPTION) ?>"
                        placeholder="The long shore at last light"
                        <?= isset($errors['caption']) ? 'aria-invalid="true" aria-describedby="error-caption"' : '' ?>>
                    <?php if (isset($errors['caption'])): ?>
                        <span class="form-error" id="error-caption"><?= e($errors['caption']) ?></span>
                    <?php else: ?>
                        <p class="form-hint">Applied to every photograph in this batch. You can delete single photos below.</p>
                    <?php endif; ?>
                </div>

                <div class="form-field form-field--full">
                    <label class="form-label-admin" for="photos">
                        Photographs <span class="req" aria-hidden="true">*</span>
                    </label>
                    <div class="upload-box">
                        <input type="file" id="photos" name="photos[]" accept="image/jpeg,image/png,image/webp"
                            multiple required data-multi-image-input="galleryFilePreview"
                            data-max-files="<?= e((string) GALLERY_MAX_FILES) ?>"
                            data-max-bytes="<?= e((string) MAX_UPLOAD_BYTES) ?>"
                            <?= isset($errors['photos']) ? 'aria-invalid="true" aria-describedby="error-photos"' : '' ?>>
                        <p class="form-hint" style="margin:0">
                            Select up to <?= e((string) GALLERY_MAX_FILES) ?> images at once. Each file is
                            MIME-checked, renamed randomly and stored in <code>/uploads/gallery</code>.
                        </p>
                        <p class="form-hint" style="margin:0" id="galleryFileCount" data-file-count aria-live="polite">
                            No photographs selected.
                        </p>
                        <div class="gallery-file-preview" id="galleryFilePreview"></div>
                    </div>
                    <?php if (isset($errors['photos'])): ?>
                        <span class="form-error" id="error-photos"><?= e($errors['photos']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="admin-card__foot">
            <div class="form-actions">
                <button class="btn-gold" type="submit">
                    <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i> Upload to gallery
                </button>
                <span class="form-actions__spacer"></span>
                <span class="form-hint" style="margin:0"><?= e((string) GALLERY_MAX_FILES) ?> photographs per batch</span>
            </div>
        </div>
    </form>
</section>

<div class="toolbar" style="margin-top:24px">
    <form class="toolbar__form gallery-toolbar-form" method="get" action="<?= e(admin_url('gallery.php')) ?>"
        role="search">
        <div class="search-wrap">
            <i class="bi bi-search" aria-hidden="true"></i>
            <label class="sr-only" for="gallery-search">Search captions</label>
            <input type="search" id="gallery-search" name="search" value="<?= e($search) ?>"
                placeholder="Search captions…" maxlength="120">
        </div>

        <label class="sr-only" for="gallery-album-filter">Filter by album</label>
        <select class="form-select-admin gallery-filter-select" id="gallery-album-filter" name="album">
            <option value="">All albums</option>
            <?php foreach ($albums as $album): ?>
                <option value="<?= e($album['album_name']) ?>"
                    <?= $albumFilter !== '' && strcasecmp($albumFilter, $album['album_name']) === 0 ? 'selected' : '' ?>>
                    <?= e($album['album_name']) ?> (<?= e((string) $album['total']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <button class="btn-line btn-sm-admin" type="submit">Filter</button>
        <?php if ($search !== '' || $albumFilter !== ''): ?>
            <a class="btn-line btn-sm-admin" href="<?= e(admin_url('gallery.php')) ?>">Clear</a>
        <?php endif; ?>
    </form>

    <span class="toolbar__count">
        <?= e((string) $totalPhotos) ?> photograph<?= $totalPhotos === 1 ? '' : 's' ?> ·
        <?= e((string) $albumCount) ?> album<?= $albumCount === 1 ? '' : 's' ?>
    </span>
</div>

<section class="admin-card" aria-labelledby="gallery-grid-title">
    <div class="admin-card__head">
        <h2 class="admin-card__title" id="gallery-grid-title">Archive <span>grid</span></h2>
        <?php if ($result['total'] > 0): ?>
            <span class="cell-muted">
                <?= e((string) $result['total']) ?> matching ·
                page <?= e((string) $result['page']) ?> of <?= e((string) $result['pages']) ?>
            </span>
        <?php endif; ?>
    </div>

    <?php if ($result['items'] === []): ?>
        <div class="empty-state">
            <div class="empty-state__icon"><i class="bi bi-images" aria-hidden="true"></i></div>
            <h3><?= $search !== '' || $albumFilter !== '' ? 'No matching photographs' : 'No photographs yet' ?></h3>
            <p>
                <?= $search !== '' || $albumFilter !== ''
                    ? 'Try a different caption, album or clear the filter.'
                    : 'Upload your first photographs above. They appear on the public gallery immediately.' ?>
            </p>
            <?php if ($search !== '' || $albumFilter !== ''): ?>
                <a class="btn-line" href="<?= e(admin_url('gallery.php')) ?>">Clear filters</a>
            <?php else: ?>
                <a class="btn-gold" href="#gallery-upload">
                    <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i> Upload photographs
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="admin-card__body">
            <div class="gallery-admin-grid">
                <?php foreach ($result['items'] as $photo): ?>
                    <?php
                    $caption = trim((string) $photo['caption']);
                    $albumName = (string) $photo['album_name'];
                    $publicAlbumUrl = url('album.php?album=' . rawurlencode($albumName));
                    ?>
                    <article class="gallery-admin-card">
                        <div class="gallery-admin-card__media">
                            <img src="<?= e(image_url($photo['image_path'])) ?>"
                                alt="<?= e($caption !== '' ? $caption : 'Gallery photograph from ' . $albumName) ?>"
                                loading="lazy" decoding="async">
                            <span class="gallery-admin-card__album"><?= e($albumName) ?></span>
                        </div>
                        <div class="gallery-admin-card__body">
                            <p class="gallery-admin-card__caption<?= $caption === '' ? ' is-empty' : '' ?>">
                                <?= e($caption !== '' ? $caption : 'No caption') ?>
                            </p>
                            <span class="gallery-admin-card__meta">
                                Added <?= e(local_datetime($photo['created_at'], 'j M Y')) ?>
                            </span>
                            <div class="gallery-admin-card__actions">
                                <a class="icon-btn" href="<?= e($publicAlbumUrl) ?>" target="_blank" rel="noopener"
                                    aria-label="View <?= e($albumName) ?> album">
                                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                </a>
                                <form method="post" action="<?= e(admin_url('gallery-delete.php')) ?>"
                                    data-confirm="Delete this photograph? The image file will be removed from the server."
                                    style="display:inline">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $photo['id'] ?>">
                                    <input type="hidden" name="album" value="<?= e($albumName) ?>">
                                    <button class="icon-btn is-danger" type="submit"
                                        aria-label="Delete photograph from <?= e($albumName) ?>">
                                        <i class="bi bi-trash3" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($result['pages'] > 1): ?>
            <div class="admin-card__foot"
                style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between">
                <span class="cell-muted">
                    Showing <?= e((string) (($result['page'] - 1) * $result['per_page'] + 1)) ?>–<?= e((string) min($result['total'], $result['page'] * $result['per_page'])) ?>
                    of <?= e((string) $result['total']) ?>
                </span>
                <?= pagination_links($result['page'], $result['pages'], ['search' => $search, 'album' => $albumFilter]) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
