<?php

declare(strict_types=1);

/**
 * Trip manager — searchable, paginated list of every destination.
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

Auth::requireLogin();

$tripModel = new Trip(Database::connection());

$search = input_string($_GET, 'search');
$page = max(1, (int) ($_GET['page'] ?? 1));
$result = $tripModel->paginate($page, TRIPS_PER_PAGE, $search);

$pageTitle = 'Trips & Destinations';
$pageHeading = 'Trips & Destinations';
$pageEyebrow = 'Content';
$pageSubtitle = 'Create, edit and retire the journeys that appear on the public archive.';
$activePage = 'trips';

$pageActions = '<a class="btn-line" href="' . e(url('journeys.php')) . '" target="_blank" rel="noopener">'
    . '<i class="bi bi-eye" aria-hidden="true"></i> Preview</a>'
    . '<a class="btn-gold" href="' . e(admin_url('trips/create.php')) . '">'
    . '<i class="bi bi-plus-lg" aria-hidden="true"></i> Add trip</a>';

require dirname(__DIR__) . '/partials/header.php';
?>

<div class="toolbar">
    <form class="toolbar__form" method="get" action="<?= e(admin_url('trips/index.php')) ?>" role="search">
        <div class="search-wrap">
            <i class="bi bi-search" aria-hidden="true"></i>
            <label class="sr-only" for="trip-search">Search trips</label>
            <input type="search" id="trip-search" name="search" value="<?= e($search) ?>"
                placeholder="Search destination, country or description…" maxlength="120">
        </div>
        <button class="btn-line btn-sm-admin" type="submit">Search</button>
        <?php if ($search !== ''): ?>
            <a class="btn-line btn-sm-admin" href="<?= e(admin_url('trips/index.php')) ?>">Clear</a>
        <?php endif; ?>
    </form>
    <span class="toolbar__count">
        <?= e((string) $result['total']) ?> trip<?= $result['total'] === 1 ? '' : 's' ?>
        <?= $search !== '' ? 'matching “' . e($search) . '”' : 'in the archive' ?>
    </span>
</div>

<section class="admin-card" aria-labelledby="trips-table-title">
    <div class="admin-card__head">
        <h2 class="admin-card__title" id="trips-table-title">
            Archive <span>index</span>
        </h2>
        <?php if ($result['total'] > 0): ?>
            <span class="cell-muted">Page <?= e((string) $result['page']) ?> of <?= e((string) $result['pages']) ?></span>
        <?php endif; ?>
    </div>

    <?php if ($result['items'] === []): ?>
        <div class="empty-state">
            <div class="empty-state__icon"><i class="bi bi-compass" aria-hidden="true"></i></div>
            <h3><?= $search !== '' ? 'No matching trips' : 'No trips yet' ?></h3>
            <p>
                <?= $search !== ''
                    ? 'Nothing matches that search. Try a shorter word, or clear the filter.'
                    : 'Add your first destination with a photograph, a travel date and a short story.' ?>
            </p>
            <?php if ($search !== ''): ?>
                <a class="btn-line" href="<?= e(admin_url('trips/index.php')) ?>">Clear search</a>
            <?php else: ?>
                <a class="btn-gold" href="<?= e(admin_url('trips/create.php')) ?>">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Add your first trip
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">Destination</th>
                        <th scope="col">Travel date</th>
                        <th scope="col">Price / budget</th>
                        <th scope="col">Added</th>
                        <th scope="col" class="cell-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['items'] as $trip): ?>
                        <tr>
                            <td>
                                <div class="trip-cell">
                                    <span class="trip-thumb">
                                        <img src="<?= e(image_url($trip['image_path'])) ?>" alt=""
                                            loading="lazy" decoding="async">
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
                            <td class="cell-muted">
                                <?= e($trip['price_or_budget'] !== '' ? $trip['price_or_budget'] : '—') ?>
                            </td>
                            <td class="cell-muted cell-nowrap">
                                <?= e(local_datetime($trip['created_at'], 'j M Y')) ?>
                            </td>
                            <td class="cell-actions">
                                <div class="row-actions">
                                    <a class="icon-btn" href="<?= e(url('journeys.php')) ?>" target="_blank"
                                        rel="noopener" aria-label="Preview public journeys">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </a>
                                    <a class="icon-btn"
                                        href="<?= e(admin_url('trips/edit.php?id=' . (int) $trip['id'])) ?>"
                                        aria-label="Edit <?= e($trip['destination_name']) ?>">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </a>
                                    <form method="post" action="<?= e(admin_url('trips/delete.php')) ?>"
                                        data-confirm="Delete “<?= e($trip['destination_name']) ?>”? This cannot be undone."
                                        style="display:inline">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $trip['id'] ?>">
                                        <button class="icon-btn is-danger" type="submit"
                                            aria-label="Delete <?= e($trip['destination_name']) ?>">
                                            <i class="bi bi-trash3" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($result['pages'] > 1): ?>
            <div class="admin-card__foot" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between">
                <span class="cell-muted">
                    Showing <?= e((string) (($result['page'] - 1) * $result['per_page'] + 1)) ?>–<?= e((string) min($result['total'], $result['page'] * $result['per_page'])) ?>
                    of <?= e((string) $result['total']) ?>
                </span>
                <?= pagination_links($result['page'], $result['pages'], ['search' => $search]) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
