<?php

declare(strict_types=1);

/**
 * Public album — the photographs stored in gallery_photos for one album.
 *
 * Accepts ?album=Album+Name (used by the admin studio and gallery items)
 * and the legacy ?place=cox-bazar form used by journey cards. The data
 * block at the end is read by js/script.js, which reuses the existing
 * cinematic slideshow with the album's database photographs.
 */

define('APP_NO_SESSION', true);

require_once __DIR__ . '/config/bootstrap.php';

$gallery = new GalleryPhoto(Database::connection());
$albums = $gallery->albums();

/** Legacy journey slugs → the album names used in the gallery. */
$placeAliases = [
    'cox-bazar'  => "Cox's Bazar",
    'teknaf'     => 'Teknaf',
    'bandarban'  => 'Bandarban',
    'sylhet'     => 'Sylhet',
    'sitakunda'  => 'Sitakunda',
    'akhilpur'   => 'Akhilpur',
    'guliakhali' => 'Guliakhali',
];

$requestedAlbum = GalleryPhoto::normaliseAlbum(input_string($_GET, 'album'));
$requestedPlace = strtolower(input_string($_GET, 'place'));

if ($requestedAlbum === '' && $requestedPlace !== '') {
    $requestedAlbum = $placeAliases[$requestedPlace]
        ?? GalleryPhoto::normaliseAlbum(str_replace('-', ' ', $requestedPlace));
}

if ($requestedAlbum === '' && $albums !== []) {
    $requestedAlbum = $albums[0]['album_name'];
}

$albumRow = null;

foreach ($albums as $album) {
    if (strcasecmp($album['album_name'], $requestedAlbum) === 0) {
        $albumRow = $album;

        break;
    }
}

$albumName = $albumRow !== null ? (string) $albumRow['album_name'] : $requestedAlbum;
$photos = $albumRow !== null ? $gallery->all($albumName, 1000) : [];
$found = $photos !== [];

$albumSlug = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $albumName));
$albumSlug = trim('album-' . trim($albumSlug, '-'), '-');

$totalPhotos = count($photos);
$albumYear = $found ? local_datetime($photos[0]['created_at'], 'Y') : (string) date('Y');
$albumDescription = $found
    ? $totalPhotos . ' photograph' . ($totalPhotos === 1 ? '' : 's') . ' gathered under “' . $albumName
        . '”, published from the admin studio.'
    : '';

$otherAlbums = array_values(array_filter(
    $albums,
    static fn (array $album): bool => strcasecmp($album['album_name'], $albumName) !== 0
));
$otherAlbums = array_slice($otherAlbums, 0, 3);

$pageTitle = $found ? $albumName : 'Album unavailable';
$pageDescription = $found
    ? 'A cinematic travel album from ' . $albumName . ' in The Journey Archive.'
    : 'This album is not in The Journey Archive yet.';

$firstPhoto = $found ? $photos[0] : null;
$firstCaption = $firstPhoto !== null ? trim((string) $firstPhoto['caption']) : '';
$firstTitle = $firstCaption !== '' ? $firstCaption : $albumName . ' — Frame 01';
$firstDate = $firstPhoto !== null ? local_datetime($firstPhoto['created_at'], 'j M Y') : '';
$firstStory = $firstCaption !== ''
    ? $firstCaption . '. A photograph from the ' . $albumName . ' album, added ' . $firstDate . '.'
    : 'A photograph from the ' . $albumName . ' album, added ' . $firstDate . '.';
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

<body data-page="album" class="album-page">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="loading-screen" id="loader" aria-hidden="true">
        <div class="loader-inner">
            <div class="loader-mark"></div>
            <p class="loader-title">THE JOURNEY ARCHIVE</p>
            <p class="loader-subtitle">ENTERING THE ALBUM</p>
            <div class="loader-line"><span></span></div>
        </div>
    </div>
    <header id="site-nav"></header>

    <main id="main-content">
        <h1 class="visually-hidden" id="album-page-title"><?= e($found ? $albumName : 'Travel Album') ?></h1>

        <?php if (!$found): ?>
            <section class="album-not-found" id="album-not-found">
                <div class="container-xxl">
                    <p class="eyebrow">Album unavailable</p>
                    <h2 class="display-title">This place isn't in the archive yet.</h2>
                    <p class="text-muted-soft">Check the link, or browse every photograph currently published.</p>
                    <a class="btn-cinematic mt-4" href="<?= e(url('gallery.php')) ?>">Back to the Gallery <span
                            class="arrow" aria-hidden="true">→</span></a>
                </div>
            </section>
        <?php else: ?>
            <div id="album-experience">
                <section class="album-stage" id="album-stage" data-album-slug="<?= e($albumSlug) ?>"
                    aria-label="Cinematic travel slideshow">
                    <div class="slide-layers" aria-hidden="false">
                        <img class="slide-layer is-active" id="slide-layer-a"
                            src="<?= e(image_url($photos[0]['image_path'])) ?>"
                            alt="<?= e($firstTitle . ' — ' . $albumName) ?>" decoding="async">
                        <img class="slide-layer" id="slide-layer-b" src="<?= e(image_url($photos[0]['image_path'])) ?>"
                            alt="" aria-hidden="true">
                    </div>
                    <div class="container-xxl album-overlay">
                        <div class="album-topbar">
                            <div>
                                <a class="album-back" href="<?= e(url('gallery.php')) ?>">← &nbsp; All Photographs</a>
                                <p class="album-title-small" id="album-title-small"><?= e($albumName) ?></p>
                            </div>
                            <div class="album-counter" role="group" aria-label="Photo counter">
                                <strong><span id="slide-number">01</span> / <span
                                        id="slide-total"><?= e(str_pad((string) $totalPhotos, 2, '0', STR_PAD_LEFT)) ?></span></strong>
                                <span>Current frame</span>
                            </div>
                        </div>

                        <div class="album-bottom">
                            <div class="album-story-panel" id="album-story-panel" aria-live="polite">
                                <span class="album-place" id="slide-location"><?= e($albumName) ?></span>
                                <h2 id="slide-title"><?= e($firstTitle) ?></h2>
                                <p class="album-story" id="slide-story"><?= e($firstStory) ?></p>
                                <p class="album-date" id="slide-date">DATE: <?= e($firstDate) ?> · YEAR:
                                    <?= e($albumYear) ?></p>
                            </div>

                            <div class="album-controls-panel">
                                <div class="album-controls" role="group" aria-label="Slideshow controls">
                                    <button class="control-button" type="button" data-slide="prev"
                                        aria-label="Previous photograph"><span class="symbol"
                                            aria-hidden="true">←</span>Prev</button>
                                    <button class="control-button" type="button" data-slide="toggle"
                                        aria-pressed="true" aria-label="Pause slideshow"><span class="symbol"
                                            aria-hidden="true">Ⅱ</span><span class="label">Pause</span></button>
                                    <button class="control-button" type="button" data-slide="next"
                                        aria-label="Next photograph"><span class="symbol"
                                            aria-hidden="true">→</span>Next</button>
                                    <button class="control-button" type="button" data-slide="fullscreen"
                                        aria-pressed="false" aria-label="Toggle fullscreen"><span class="symbol"
                                            aria-hidden="true">⛶</span>Full</button>
                                </div>
                                <div class="progress-track" aria-hidden="true"><span class="progress-fill"
                                        id="slide-progress"></span></div>
                                <p class="control-hint">Keyboard: ← → navigate · Space play/pause · F fullscreen</p>
                                <div class="slide-thumbs" id="slide-thumbs" role="group"
                                    aria-label="Choose a photograph">
                                    <?php foreach ($photos as $index => $photo): ?>
                                        <?php
                                        $thumbCaption = trim((string) $photo['caption']);
                                        $thumbTitle = $thumbCaption !== ''
                                            ? $thumbCaption
                                            : $albumName . ' — Frame ' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
                                        ?>
                                        <button class="slide-thumb<?= $index === 0 ? ' active' : '' ?>" type="button"
                                            data-slide-index="<?= (int) $index ?>"
                                            aria-label="Show photograph <?= e((string) ($index + 1)) ?>: <?= e($thumbTitle) ?>">
                                            <img src="<?= e(image_url($photo['image_path'])) ?>" alt="" loading="lazy"
                                                decoding="async">
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="container-xxl" aria-label="Album information">
                    <div class="album-info-strip">
                        <div><span>Archive year</span><strong class="travel-year"
                                id="album-year"><?= e($albumYear) ?></strong></div>
                        <div><span>Album</span><strong id="album-region">Personal archive</strong></div>
                        <div><span>Archive</span><strong
                                id="album-count"><?= e(str_pad((string) $totalPhotos, 2, '0', STR_PAD_LEFT)) ?>
                                FRAMES</strong></div>
                    </div>
                </section>

                <section class="section-pad-sm" aria-labelledby="story-list-title">
                    <div class="container-xxl">
                        <div class="section-heading" data-reveal>
                            <div><span class="section-index">01 / Field notes</span>
                                <h2 id="story-list-title">Stories in This Album</h2>
                            </div>
                            <p id="album-description"><?= e($albumDescription) ?></p>
                        </div>
                        <div class="story-list" id="story-list" role="group" aria-label="All photograph stories">
                            <?php foreach ($photos as $index => $photo): ?>
                                <?php
                                $storyCaption = trim((string) $photo['caption']);
                                $storyDate = local_datetime($photo['created_at'], 'j M Y');
                                $storyTitle = $storyCaption !== ''
                                    ? $storyCaption
                                    : $albumName . ' — Frame ' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
                                $storyText = $storyCaption !== ''
                                    ? $storyCaption . '. A photograph from the ' . $albumName . ' album, added ' . $storyDate . '.'
                                    : 'A photograph from the ' . $albumName . ' album, added ' . $storyDate . '.';
                                ?>
                                <a class="story-entry<?= $index === 0 ? ' active' : '' ?>" href="#album-stage"
                                    data-slide-index="<?= (int) $index ?>">
                                    <span>FRAME <?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?> &middot;
                                        <?= e($storyDate) ?></span>
                                    <h3><?= e($storyTitle) ?></h3>
                                    <p><?= e($storyText) ?></p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>

                <section class="section-pad-sm skills-section" aria-labelledby="continue-title">
                    <div class="container-xxl">
                        <div class="section-heading" data-reveal>
                            <div><span class="section-index">02 / Keep exploring</span>
                                <h2 id="continue-title">The Journey Continues</h2>
                            </div>
                            <p>One place always leads to another. Choose the next chapter of the archive.</p>
                        </div>
                        <div class="mini-journeys" id="other-journeys">
                            <?php foreach ($otherAlbums as $index => $other): ?>
                                <a class="mini-journey"
                                    href="<?= e(url('album.php?album=' . rawurlencode($other['album_name']))) ?>"
                                    data-reveal style="--reveal-delay:<?= e((string) ($index * 0.08)) ?>s"
                                    aria-label="Continue to <?= e($other['album_name']) ?>">
                                    <img src="<?= e(image_url($other['cover'])) ?>"
                                        alt="Photograph from <?= e($other['album_name']) ?>" loading="lazy"
                                        decoding="async">
                                    <div><span><?= e(str_pad((string) $other['total'], 2, '0', STR_PAD_LEFT)) ?>
                                            memories</span>
                                        <h3><?= e($other['album_name']) ?></h3>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-5" data-reveal><a class="btn-ghost"
                                href="<?= e(url('gallery.php')) ?>">Full Gallery <span class="arrow"
                                    aria-hidden="true">→</span></a></div>
                    </div>
                </section>
            </div>

            <!-- Read by js/script.js to hydrate the existing slideshow with database photos. -->
            <div id="album-db-data" hidden data-album-slug="<?= e($albumSlug) ?>"
                data-album-title="<?= e($albumName) ?>" data-album-year="<?= e($albumYear) ?>"
                data-album-region="Personal archive" data-album-description="<?= e($albumDescription) ?>">
                <?php foreach ($photos as $index => $photo): ?>
                    <?php
                    $dataCaption = trim((string) $photo['caption']);
                    $dataDate = local_datetime($photo['created_at'], 'j M Y');
                    $dataTitle = $dataCaption !== ''
                        ? $dataCaption
                        : $albumName . ' — Frame ' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
                    $dataStory = $dataCaption !== ''
                        ? $dataCaption . '. A photograph from the ' . $albumName . ' album, added ' . $dataDate . '.'
                        : 'A photograph from the ' . $albumName . ' album, added ' . $dataDate . '.';
                    ?>
                    <span data-photo data-title="<?= e($dataTitle) ?>" data-story="<?= e($dataStory) ?>"
                        data-date="<?= e($dataDate) ?>" data-image="<?= e(image_url($photo['image_path'])) ?>"
                        data-location="<?= e($albumName) ?>"></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <div id="site-footer"></div>
</body>

</html>
