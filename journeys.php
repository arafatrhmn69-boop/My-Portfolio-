<?php

declare(strict_types=1);

/**
 * ============================================================
 *  PUBLIC INTEGRATION DEMO — journeys.php
 * ============================================================
 *  A working example of the public half of the admin panel:
 *  every card below is read from the SQLite `trips` table that the
 *  admin console writes to.
 *
 *  Copy the marked loop into your own index.php (see ADMIN-PANEL.md
 *  for the exact snippet) and delete this page if you don't need it.
 * ============================================================
 */

define('APP_NO_SESSION', true);

require_once __DIR__ . '/config/bootstrap.php';

$tripModel = new Trip(Database::connection());
$trips = $tripModel->all(60);

$totalTrips = count($trips);
$totalCountries = count(array_unique(array_column($trips, 'country')));
$latestTrip = $trips[0] ?? null;

$pageTitle = 'Journeys · ' . APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#07090a">
    <meta name="description"
        content="Every journey in The Journey Archive, rendered dynamically from the SQLite database.">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Manrope:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">

    <style>
        /* Only the page chrome lives here; the journey cards reuse the
           existing design system from css/style.css. */
        .demo-topbar {
            border-bottom: 1px solid var(--line-soft);
            background: rgba(7, 9, 10, .88);
            backdrop-filter: blur(14px);
        }

        .demo-topbar__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 16px 0;
        }

        .demo-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: var(--serif);
            font-size: 1rem;
            letter-spacing: .16em;
            color: var(--ink);
        }

        .demo-brand span {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            border: 1px solid rgba(200, 166, 108, .5);
            border-radius: 50%;
            font-size: .85rem;
            color: var(--gold);
        }

        .demo-nav {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px 22px;
            font-size: .62rem;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .demo-nav a:hover { color: var(--gold-light); }
        .demo-nav .demo-admin { color: var(--gold); }

        .demo-note {
            display: flex;
            gap: 12px;
            margin: 0 0 34px;
            padding: 16px 18px;
            border: 1px solid var(--line);
            border-left: 2px solid var(--gold);
            background: var(--surface);
            color: var(--muted);
            font-size: .82rem;
            line-height: 1.7;
        }

        .demo-note strong { color: var(--ink-soft); }
        .demo-note code { color: var(--gold-light); }

        .demo-empty {
            padding: 70px 24px;
            border: 1px solid var(--line);
            background: var(--surface);
            text-align: center;
        }

        .demo-empty h3 {
            margin: 0 0 10px;
            font-family: var(--serif);
            font-size: 2.2rem;
            font-weight: 400;
        }

        .demo-empty p { max-width: 460px; margin: 0 auto 22px; color: var(--muted); font-size: .9rem; }

        .demo-footer {
            padding: 42px 0;
            border-top: 1px solid var(--line);
            background: var(--footer-bg);
            color: var(--muted-dim);
            font-size: .72rem;
            letter-spacing: .04em;
        }

        .demo-footer .container-xxl {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .demo-footer a { color: var(--gold); }
        .demo-footer a:hover { color: var(--gold-light); }

        .form-status[data-state="error"] { color: #e89a92; }
        .form-status[data-state="success"] { color: var(--gold-light); }
        .form-status[data-state="sending"] { color: var(--muted); }

        /* Semantic list, reset to the site's grid look. */
        .albums-grid {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        /* Descriptions are hover-revealed on desktop only; touch devices
           should see the story immediately. */
        @media (hover: none), (max-width: 991.98px) {
            .journey-card .card-description { opacity: 1; transform: none; }
            .journey-card { transition: none; }
        }
    </style>
</head>

<body data-page="journeys">
    <a class="skip-link" href="#main-content">Skip to content</a>

    <header class="demo-topbar">
        <div class="container-xxl demo-topbar__inner">
            <a class="demo-brand" href="<?= e(url('index.html')) ?>">
                <span aria-hidden="true">JA</span> THE JOURNEY ARCHIVE
            </a>
            <nav class="demo-nav" aria-label="Demo navigation">
                <a href="<?= e(url('index.html')) ?>">Home</a>
                <a href="<?= e(url('albums.html')) ?>">Albums</a>
                <a href="<?= e(url('gallery.html')) ?>">Gallery</a>
                <a class="demo-admin" href="<?= e(admin_url('login.php')) ?>">Admin</a>
            </nav>
        </div>
    </header>

    <main id="main-content">
        <section class="page-hero" data-watermark="JOURNEYS" aria-labelledby="journeys-title">
            <div class="container-xxl page-hero-inner">
                <nav class="breadcrumb-custom" aria-label="Breadcrumb">
                    <a href="<?= e(url('index.html')) ?>">Home</a><span>/</span><span>Journeys</span>
                </nav>
                <p class="eyebrow">Rendered live from SQLite</p>
                <h1 id="journeys-title">Every <em>Journey</em></h1>
                <p>
                    This page is the public half of the admin panel. Every destination below was added,
                    edited or removed in the console — no hard-coded cards, no duplicate data.
                </p>
                <div class="page-hero-meta">
                    <span><span><?= e((string) $totalTrips) ?></span> Journeys</span>
                    <span><span><?= e((string) $totalCountries) ?></span> Countries</span>
                    <?php if ($latestTrip !== null): ?>
                        <span>Most recent: <?= e(format_date($latestTrip['travel_date'], 'F Y')) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="section-pad-sm" aria-labelledby="places-title">
            <div class="container-xxl">
                <div class="section-heading">
                    <div>
                        <span class="section-index">01 / The living archive</span>
                        <h2 id="places-title">Places with Stories</h2>
                    </div>
                    <p>Each card is one row in the <code>trips</code> table. Publish a new one in the admin panel and
                        it appears here on the next page load.</p>
                </div>

                <div class="demo-note">
                    <span aria-hidden="true">↳</span>
                    <p style="margin:0">
                        <strong>Integration demo.</strong> This file uses <code>Trip::all()</code> and the same
                        <code>.journey-card</code> markup as the rest of the site. The copy-ready loop for your own
                        <code>index.php</code> is documented in <code>ADMIN-PANEL.md</code>.
                    </p>
                </div>

                <?php if ($trips === []): ?>
                    <div class="demo-empty">
                        <h3>No journeys published yet</h3>
                        <p>Add the first destination in the admin console and this archive will fill itself.</p>
                        <a class="btn-cinematic" href="<?= e(admin_url('trips/create.php')) ?>">
                            Add a trip <span class="arrow" aria-hidden="true">↗</span>
                        </a>
                    </div>
                <?php else: ?>
                    <!-- =====================================================
                         PUBLIC LOOP — copy the pattern below into index.php.
                         Every echoed value is escaped with e(); every image
                         path is resolved through image_url().
                         ===================================================== -->
                    <ul class="albums-grid" aria-label="All journeys">
                        <?php foreach ($trips as $index => $trip): ?>
                            <?php
                            $tripYear = substr((string) $trip['travel_date'], 0, 4);
                            $tripImage = image_url($trip['image_path']);
                            $tripAlt = 'Travel photograph representing ' . $trip['destination_name'];
                            ?>
                            <li class="journey-card-wrap">
                                <article class="journey-card">
                                    <img src="<?= e($tripImage) ?>" alt="<?= e($tripAlt) ?>"
                                        loading="<?= $index < 2 ? 'eager' : 'lazy' ?>" decoding="async">

                                    <div class="card-top">
                                        <span class="card-number">JOURNEY <?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                                        <span><?= e($tripYear) ?></span>
                                    </div>

                                    <div class="card-bottom">
                                        <span class="card-region"><?= e($trip['country']) ?></span>
                                        <h3><?= e($trip['destination_name']) ?></h3>
                                        <p class="card-description"><?= e(excerpt((string) $trip['description'], 170)) ?></p>
                                        <div class="card-meta-row">
                                            <span><?= e(format_date((string) $trip['travel_date'], 'j M Y')) ?></span>
                                            <?php if ((string) $trip['price_or_budget'] !== ''): ?>
                                                <span><?= e($trip['price_or_budget']) ?></span>
                                            <?php endif; ?>
                                            <span class="card-cta">
                                                From the archive <span class="arrow" aria-hidden="true">↗</span>
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <p class="section-footnote">
                    Source: <span class="travel-year">database/portfolio.sqlite</span> ·
                    rendered by journeys.php on <?= e(date('j M Y')) ?>.
                </p>
            </div>
        </section>

        <section class="contact-section section-pad" id="contact" aria-labelledby="demo-contact-title">
            <div class="container-xxl contact-layout">
                <div class="contact-copy">
                    <p class="eyebrow">The other half of the integration</p>
                    <h2 id="demo-contact-title">Leave a Note</h2>
                    <p>
                        This form posts to <code>api/contact.php</code>, which validates the input, checks a CSRF
                        token and writes the message into the SQLite <code>inquiries</code> table. Open
                        <a href="<?= e(admin_url('inquiries/index.php')) ?>">Admin → Inquiries</a> to read it.
                    </p>
                    <div class="contact-detail">
                        Endpoint: <span class="travel-year">api/contact.php</span>
                    </div>
                </div>

                <div>
                    <form id="demo-contact-form" novalidate>
                        <div class="form-grid">
                            <div class="form-field">
                                <label for="demo-name">Name</label>
                                <input id="demo-name" name="sender_name" type="text" maxlength="120"
                                    autocomplete="name" placeholder="Your name" required>
                            </div>
                            <div class="form-field">
                                <label for="demo-email">Email</label>
                                <input id="demo-email" name="email" type="email" maxlength="190"
                                    autocomplete="email" placeholder="you@example.com" required>
                            </div>
                            <div class="form-field full">
                                <label for="demo-message">Message</label>
                                <textarea id="demo-message" name="message" maxlength="5000" required
                                    placeholder="Tell me about a place, a story, or this website..."></textarea>
                            </div>
                        </div>

                        <!-- Honeypot: hidden from humans, irresistible to bots. -->
                        <input type="text" name="website" tabindex="-1" autocomplete="off"
                            aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px">

                        <button class="btn-cinematic" type="submit">
                            Send Message <span class="arrow" aria-hidden="true">→</span>
                        </button>
                        <p class="form-status" id="demo-contact-status" role="status" aria-live="polite"></p>
                        <p class="form-note">
                            Demo only — no email is sent. The message is stored in
                            <span class="travel-year">database/portfolio.sqlite</span>.
                        </p>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer class="demo-footer">
        <div class="container-xxl">
            <span>© <?= e(date('Y')) ?> THE JOURNEY ARCHIVE · A personal travel archive</span>
            <span>
                <a href="<?= e(admin_url('login.php')) ?>">Admin console</a> ·
                <a href="<?= e(url('api/trips.php')) ?>">JSON feed</a>
            </span>
        </div>
    </footer>
    <script src="<?= e(url('assets/contact-demo.js')) ?>" defer></script>
</body>

</html>
