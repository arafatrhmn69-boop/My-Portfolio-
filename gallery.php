<?php

declare(strict_types=1);

/**
 * Public gallery — every photograph stored in gallery_photos.
 *
 * The admin panel writes to the same SQLite table, so uploading or
 * deleting a photograph updates this page immediately. Filtering and
 * the fullscreen lightbox are handled by js/script.js.
 */

define('APP_NO_SESSION', true);

require_once __DIR__ . '/config/bootstrap.php';

$gallery = new GalleryPhoto(Database::connection());
$photos = $gallery->all('', 1000);
$albums = $gallery->albums();
$totalPhotos = $gallery->count();
$albumCount = count($albums);
$visiblePhotos = count($photos);

$pageTitle = 'Gallery';
$pageDescription = 'A filterable editorial travel photography gallery from The Journey Archive.';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#07090a">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <script src="<?= e(url('js/theme-init.js')) ?>"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Manrope:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
    <noscript>
        <style>
            #loader { display: none !important; }
        </style>
    </noscript>
    <script src="<?= e(url('js/script.js')) ?>" defer></script>
</head>

<body data-page="gallery">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="loading-screen" id="loader" aria-hidden="true">
        <div class="loader-inner">
            <div class="loader-mark"></div>
            <p class="loader-title">THE JOURNEY ARCHIVE</p>
            <p class="loader-subtitle">OPENING THE GALLERY</p>
            <div class="loader-line"><span></span></div>
        </div>
    </div>
    <header id="site-nav"></header>

    <main id="main-content">
        <section class="page-hero" data-watermark="FRAMES" aria-labelledby="page-title">
            <div class="container-xxl page-hero-inner">
                <nav class="breadcrumb-custom" aria-label="Breadcrumb">
                    <a href="<?= e(url('index.html')) ?>">Home</a><span>/</span><span>Gallery</span>
                </nav>
                <p class="eyebrow load-reveal" style="--load-delay:.1s">Selected photographs from every place</p>
                <h1 class="load-reveal" id="page-title" style="--load-delay:.18s">The <em>Gallery</em></h1>
                <p class="load-reveal" style="--load-delay:.28s">An editorial wall of frames. Filter by album, hover to
                    look closer, then open a photograph to read its story.</p>
                <div class="page-hero-meta">
                    <span><span><?= e(str_pad((string) $totalPhotos, 2, '0', STR_PAD_LEFT)) ?></span> Photographs</span>
                    <span><span><?= e(str_pad((string) $albumCount, 2, '0', STR_PAD_LEFT)) ?></span> Albums</span>
                    <span>Fullscreen Lightbox</span>
                </div>
            </div>
        </section>

        <section class="section-pad" id="gallery-collection" aria-labelledby="gallery-title">
            <div class="container-xxl">
                <div class="section-heading" data-reveal>
                    <div><span class="section-index">01 / The collection</span>
                        <h2 id="gallery-title">Every Frame</h2>
                    </div>
                    <p>Photographs are published from the admin studio. Uploading or deleting a frame there changes
                        this wall immediately — no code edits required.</p>
                </div>

                <div class="gallery-toolbar">
                    <div class="gallery-filters" id="gallery-filters" role="group"
                        aria-label="Filter photographs by album">
                        <button class="filter-btn active" type="button" data-filter="all"
                            aria-pressed="true">All</button>
                        <?php foreach ($albums as $album): ?>
                            <button class="filter-btn" type="button"
                                data-filter="<?= e($album['album_name']) ?>" aria-pressed="false">
                                <?= e($album['album_name']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <p class="gallery-count" id="gallery-count" aria-live="polite">
                        <?= e(str_pad((string) $visiblePhotos, 2, '0', STR_PAD_LEFT)) ?>
                        photograph<?= $visiblePhotos === 1 ? '' : 's' ?>
                    </p>
                </div>

                <?php if ($photos === []): ?>
                    <div class="gallery-empty" data-reveal>
                        <span class="eyebrow">The wall is waiting</span>
                        <h3>No photographs have been published yet.</h3>
                        <p>Sign in to the admin studio to upload the first frame, or explore the written journeys in
                            the meantime.</p>
                        <a class="btn-ghost" href="<?= e(url('albums.html')) ?>">Explore the Journeys <span
                                class="arrow" aria-hidden="true">→</span></a>
                    </div>
                <?php else: ?>
                    <div class="gallery-masonry" id="gallery-grid" data-gallery-source="database" role="group"
                        aria-label="Travel photograph gallery">
                        <?php foreach ($photos as $index => $photo): ?>
                            <?php
                            $albumName = (string) $photo['album_name'];
                            $caption = trim((string) $photo['caption']);
                            $date = local_datetime($photo['created_at'], 'j M Y');
                            $title = $caption !== '' ? $caption : $albumName . ' — Frame ' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
                            $story = $caption !== ''
                                ? $caption . '. A photograph from the ' . $albumName . ' album, added ' . $date . '.'
                                : 'A photograph from the ' . $albumName . ' album, added ' . $date . '.';
                            $size = ['tall', 'wide', 'square', 'wide', 'tall', 'square'][$index % 6];
                            $image = image_url($photo['image_path']);
                            ?>
                            <button class="gallery-item gallery-enter" type="button" data-gallery-entry
                                data-album="<?= e($albumName) ?>" data-title="<?= e($title) ?>"
                                data-story="<?= e($story) ?>" data-date="<?= e($date) ?>"
                                data-image="<?= e($image) ?>" data-index="<?= (int) $index ?>"
                                data-size="<?= e($size) ?>"
                                style="animation-delay:<?= e((string) (($index % 8) * 0.04)) ?>s"
                                aria-label="Open <?= e($title) ?> from <?= e($albumName) ?> in fullscreen">
                                <img src="<?= e($image) ?>" alt="<?= e($title . ' — ' . $albumName) ?>" loading="lazy"
                                    decoding="async">
                                <span class="gallery-overlay">
                                    <span class="gallery-place"><?= e($albumName) ?> &middot; <?= e($date) ?></span>
                                    <strong><?= e($caption !== '' ? $caption : 'Untitled frame') ?></strong>
                                    <small>Click to read the story</small>
                                </span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="text-center mt-5" data-reveal><a class="btn-ghost"
                        href="<?= e(url('albums.html')) ?>">Enter an Album <span class="arrow"
                            aria-hidden="true">↗</span></a></div>
            </div>
        </section>
    </main>

    <!-- Accessible fullscreen lightbox. CSS hides it until JavaScript opens it. -->
    <div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-hidden="true" inert
        aria-labelledby="lightbox-title">
        <button class="circle-btn lightbox-close" id="lightbox-close" type="button" data-lightbox-close
            aria-label="Close fullscreen photograph">×</button>
        <div class="lightbox-inner">
            <div class="lightbox-photo"><img id="lightbox-image" src="<?= e(url('assets/trip-placeholder.svg')) ?>"
                    alt=""></div>
            <div class="lightbox-info">
                <span class="gallery-place" id="lightbox-place">ALBUM</span>
                <h2 id="lightbox-title">PHOTO TITLE</h2>
                <p id="lightbox-story">PHOTO STORY</p>
                <p class="lightbox-date" id="lightbox-date">DATE</p>
                <div class="lightbox-controls">
                    <button class="circle-btn" type="button" data-lightbox="prev"
                        aria-label="Previous photograph">←</button>
                    <button class="circle-btn" type="button" data-lightbox="next"
                        aria-label="Next photograph">→</button>
                    <span class="lightbox-counter"><span id="lightbox-number">01</span> / <span
                            id="lightbox-total">00</span></span>
                </div>
            </div>
        </div>
    </div>

    <div id="site-footer"></div>
</body>

</html>
