<?php

declare(strict_types=1);

/**
 * Dashboard overview: summary cards, recent trips and recent inquiries.
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

Auth::requireLogin();

$pdo = Database::connection();
$tripModel = new Trip($pdo);
$inquiryModel = new Inquiry($pdo);

$tripCount = $tripModel->count();
$inquiryCount = $inquiryModel->count();
$countryCount = $tripModel->countriesCount();
$inquiriesThisMonth = $inquiryModel->countSince(gmdate('Y-m-01 00:00:00'));

$recentTrips = $tripModel->recent(4);
$recentInquiries = $inquiryModel->recent(5);

$pageTitle = 'Dashboard';
$pageHeading = 'Dashboard';
$pageEyebrow = 'Overview';
$pageSubtitle = 'A quiet control room for the travel archive — publish a journey, read a note from a visitor, keep the story current.';
$activePage = 'dashboard';

$pageActions = '<a class="btn-line" href="' . e(url()) . '" target="_blank" rel="noopener">'
    . '<i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> View site</a>'
    . '<a class="btn-gold" href="' . e(admin_url('trips/create.php')) . '">'
    . '<i class="bi bi-plus-lg" aria-hidden="true"></i> Add trip</a>';

require __DIR__ . '/partials/header.php';
?>

<div class="stat-grid">
    <article class="stat-card">
        <div class="stat-card__top">
            <span class="stat-card__label">Trips &amp; destinations</span>
            <span class="stat-card__icon"><i class="bi bi-map" aria-hidden="true"></i></span>
        </div>
        <span class="stat-card__value"><?= e((string) $tripCount) ?></span>
        <p class="stat-card__note">
            <?php if ($recentTrips !== []): ?>
                Latest: <strong><?= e($recentTrips[0]['destination_name']) ?></strong>
            <?php else: ?>
                No journeys published yet
            <?php endif; ?>
        </p>
        <a class="stat-card__link" href="<?= e(admin_url('trips/index.php')) ?>">
            <span>Manage trips</span>
        </a>
    </article>

    <article class="stat-card">
        <div class="stat-card__top">
            <span class="stat-card__label">Inquiries received</span>
            <span class="stat-card__icon"><i class="bi bi-envelope-paper" aria-hidden="true"></i></span>
        </div>
        <span class="stat-card__value"><?= e((string) $inquiryCount) ?></span>
        <p class="stat-card__note">
            <strong><?= e((string) $inquiriesThisMonth) ?></strong>
            new this month
        </p>
        <a class="stat-card__link" href="<?= e(admin_url('inquiries/index.php')) ?>">
            <span>Read inquiries</span>
        </a>
    </article>

    <article class="stat-card">
        <div class="stat-card__top">
            <span class="stat-card__label">Countries covered</span>
            <span class="stat-card__icon"><i class="bi bi-globe2" aria-hidden="true"></i></span>
        </div>
        <span class="stat-card__value"><?= e((string) $countryCount) ?></span>
        <p class="stat-card__note">Distinct countries in the archive</p>
        <a class="stat-card__link" href="<?= e(admin_url('trips/index.php')) ?>">
            <span>Browse destinations</span>
        </a>
    </article>

    <article class="stat-card">
        <div class="stat-card__top">
            <span class="stat-card__label">Contact form</span>
            <span class="stat-card__icon"><i class="bi bi-chat-square-text" aria-hidden="true"></i></span>
        </div>
        <span class="stat-card__value"><?= $inquiryCount > 0 ? 'Live' : 'Ready' ?></span>
        <p class="stat-card__note">Writing to the SQLite inquiries table</p>
        <a class="stat-card__link" href="<?= e(admin_url('inquiries/index.php')) ?>">
            <span>Open inbox</span>
        </a>
    </article>
</div>

<div class="admin-columns">
    <section class="admin-card" aria-labelledby="recent-trips-title">
        <div class="admin-card__head">
            <h2 class="admin-card__title" id="recent-trips-title">Recent <span>journeys</span></h2>
            <a class="admin-card__link" href="<?= e(admin_url('trips/index.php')) ?>">View all</a>
        </div>

        <?php if ($recentTrips === []): ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-map" aria-hidden="true"></i></div>
                <h3>No journeys yet</h3>
                <p>Your archive is waiting for its first story. Add a destination, a travel date and a photograph.</p>
                <a class="btn-gold" href="<?= e(admin_url('trips/create.php')) ?>">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Add your first trip
                </a>
            </div>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Destination</th>
                            <th scope="col">Travel date</th>
                            <th scope="col">Budget</th>
                            <th scope="col" class="cell-actions">Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTrips as $trip): ?>
                            <tr>
                                <td>
                                    <div class="trip-cell">
                                        <span class="trip-thumb">
                                            <img src="<?= e(image_url($trip['image_path'])) ?>"
                                                alt="" loading="lazy" decoding="async">
                                        </span>
                                        <span>
                                            <span class="trip-cell__title"><?= e($trip['destination_name']) ?></span>
                                            <span class="trip-cell__meta"><?= e($trip['country']) ?></span>
                                        </span>
                                    </div>
                                </td>
                                <td class="cell-nowrap">
                                    <time datetime="<?= e($trip['travel_date']) ?>">
                                        <?= e(format_date($trip['travel_date'], 'j M Y')) ?>
                                    </time>
                                </td>
                                <td class="cell-muted"><?= e($trip['price_or_budget'] !== '' ? $trip['price_or_budget'] : '—') ?></td>
                                <td class="cell-actions">
                                    <a class="icon-btn" href="<?= e(admin_url('trips/edit.php?id=' . (int) $trip['id'])) ?>"
                                        aria-label="Edit <?= e($trip['destination_name']) ?>">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="admin-card" aria-labelledby="recent-inquiries-title">
        <div class="admin-card__head">
            <h2 class="admin-card__title" id="recent-inquiries-title">Latest <span>inquiries</span></h2>
            <a class="admin-card__link" href="<?= e(admin_url('inquiries/index.php')) ?>">View all</a>
        </div>

        <?php if ($recentInquiries === []): ?>
            <div class="empty-state">
                <div class="empty-state__icon"><i class="bi bi-envelope-paper" aria-hidden="true"></i></div>
                <h3>The inbox is quiet</h3>
                <p>Messages sent through the public contact form will appear here.</p>
                <a class="btn-line" href="<?= e(url('journeys.php')) ?>">
                    <i class="bi bi-eye" aria-hidden="true"></i> Preview the public page
                </a>
            </div>
        <?php else: ?>
            <ul class="recent-list">
                <?php foreach ($recentInquiries as $inquiry): ?>
                    <?php
                    $inquiryInitial = mb_strtoupper(
                        mb_substr(trim((string) $inquiry['sender_name']), 0, 1, 'UTF-8'),
                        'UTF-8'
                    );
                    ?>
                    <li>
                        <a class="recent-item" href="<?= e(admin_url('inquiries/view.php?id=' . (int) $inquiry['id'])) ?>">
                            <span class="inquiry-avatar" aria-hidden="true"><?= e($inquiryInitial !== '' ? $inquiryInitial : '?') ?></span>
                            <span class="recent-item__body">
                                <span class="recent-item__title"><?= e($inquiry['sender_name']) ?></span>
                                <span class="recent-item__meta"><?= e(excerpt($inquiry['message'], 52)) ?></span>
                            </span>
                            <span class="recent-item__time"><?= e(local_datetime($inquiry['created_at'], 'j M')) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="admin-card__foot">
            <a class="admin-card__link" href="<?= e(admin_url('inquiries/index.php')) ?>">
                Open the inquiry manager <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>
    </section>
</div>

<section class="admin-card" style="margin-top:18px" aria-labelledby="integration-title">
    <div class="admin-card__head">
        <h2 class="admin-card__title" id="integration-title">Public site <span>integration</span></h2>
        <span class="badge-soft badge-soft--gold">Ready</span>
    </div>
    <div class="admin-card__body">
        <div class="form-grid">
            <div>
                <p class="form-hint" style="margin-top:0">
                    <strong style="color:var(--a-ink-soft)">Rendered trip cards</strong><br>
                    Include <code>config/bootstrap.php</code> in your public <code>index.php</code> and loop over
                    <code>Trip::all()</code>. A full copy-ready snippet is in <code>ADMIN-PANEL.md</code>.
                </p>
                <a class="btn-line btn-sm-admin" href="<?= e(url('journeys.php')) ?>" target="_blank" rel="noopener">
                    <i class="bi bi-eye" aria-hidden="true"></i> Preview journeys.php
                </a>
            </div>
            <div>
                <p class="form-hint" style="margin-top:0">
                    <strong style="color:var(--a-ink-soft)">JSON feed &amp; contact endpoint</strong><br>
                    <code><?= e(url('api/trips.php')) ?></code> returns public trips as JSON.<br>
                    <code><?= e(url('api/contact.php')) ?></code> stores contact-form messages.
                </p>
                <a class="btn-line btn-sm-admin" href="<?= e(url('api/trips.php')) ?>" target="_blank" rel="noopener">
                    <i class="bi bi-braces" aria-hidden="true"></i> Open JSON feed
                </a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
