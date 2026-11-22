<?php

declare(strict_types=1);

/**
 * Inquiry manager — read and delete contact-form messages.
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

Auth::requireLogin();

$inquiryModel = new Inquiry(Database::connection());

$search = input_string($_GET, 'search');
$page = max(1, (int) ($_GET['page'] ?? 1));
$result = $inquiryModel->paginate($page, INQUIRIES_PER_PAGE, $search);

$pageTitle = 'Inquiries';
$pageHeading = 'Inquiries';
$pageEyebrow = 'Messages';
$pageSubtitle = 'Every note left through the public contact form, stored in SQLite. Replies happen from your own email client.';
$activePage = 'inquiries';

$pageActions = '<a class="btn-line" href="' . e(url()) . '#contact" target="_blank" rel="noopener">'
    . '<i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Open contact form</a>';

require dirname(__DIR__) . '/partials/header.php';
?>

<div class="toolbar">
    <form class="toolbar__form" method="get" action="<?= e(admin_url('inquiries/index.php')) ?>" role="search">
        <div class="search-wrap">
            <i class="bi bi-search" aria-hidden="true"></i>
            <label class="sr-only" for="inquiry-search">Search inquiries</label>
            <input type="search" id="inquiry-search" name="search" value="<?= e($search) ?>"
                placeholder="Search name, email or message…" maxlength="120">
        </div>
        <button class="btn-line btn-sm-admin" type="submit">Search</button>
        <?php if ($search !== ''): ?>
            <a class="btn-line btn-sm-admin" href="<?= e(admin_url('inquiries/index.php')) ?>">Clear</a>
        <?php endif; ?>
    </form>
    <span class="toolbar__count">
        <?= e((string) $result['total']) ?> message<?= $result['total'] === 1 ? '' : 's' ?>
        <?= $search !== '' ? 'matching “' . e($search) . '”' : 'received' ?>
    </span>
</div>

<section class="admin-card" aria-labelledby="inquiries-table-title">
    <div class="admin-card__head">
        <h2 class="admin-card__title" id="inquiries-table-title">Inbox <span>archive</span></h2>
        <?php if ($result['total'] > 0): ?>
            <span class="cell-muted">Page <?= e((string) $result['page']) ?> of <?= e((string) $result['pages']) ?></span>
        <?php endif; ?>
    </div>

    <?php if ($result['items'] === []): ?>
        <div class="empty-state">
            <div class="empty-state__icon"><i class="bi bi-envelope-paper" aria-hidden="true"></i></div>
            <h3><?= $search !== '' ? 'No matching messages' : 'No messages yet' ?></h3>
            <p>
                <?= $search !== ''
                    ? 'Try a different name, email address or keyword.'
                    : 'When a visitor submits the public contact form, the message will appear here.' ?>
            </p>
            <?php if ($search !== ''): ?>
                <a class="btn-line" href="<?= e(admin_url('inquiries/index.php')) ?>">Clear search</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">Sender</th>
                        <th scope="col">Message</th>
                        <th scope="col">Received</th>
                        <th scope="col" class="cell-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($result['items'] as $inquiry): ?>
                        <?php
                        $initial = mb_strtoupper(
                            mb_substr(trim((string) $inquiry['sender_name']), 0, 1, 'UTF-8'),
                            'UTF-8'
                        );
                        ?>
                        <tr>
                            <td>
                                <div class="inquiry-row">
                                    <span class="inquiry-avatar" aria-hidden="true"><?= e($initial !== '' ? $initial : '?') ?></span>
                                    <span>
                                        <span class="inquiry-name"><?= e($inquiry['sender_name']) ?></span>
                                        <span class="inquiry-email"><?= e($inquiry['email']) ?></span>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <p class="inquiry-excerpt"><?= e(excerpt($inquiry['message'], 96)) ?></p>
                            </td>
                            <td class="cell-muted cell-nowrap">
                                <?= e(local_datetime($inquiry['created_at'], 'j M Y, g:i A')) ?>
                            </td>
                            <td class="cell-actions">
                                <div class="row-actions">
                                    <a class="icon-btn"
                                        href="<?= e(admin_url('inquiries/view.php?id=' . (int) $inquiry['id'])) ?>"
                                        aria-label="Read message from <?= e($inquiry['sender_name']) ?>">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </a>
                                    <form method="post" action="<?= e(admin_url('inquiries/delete.php')) ?>"
                                        data-confirm="Delete the message from <?= e($inquiry['sender_name']) ?>? This cannot be undone."
                                        style="display:inline">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                                        <button class="icon-btn is-danger" type="submit"
                                            aria-label="Delete message from <?= e($inquiry['sender_name']) ?>">
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
