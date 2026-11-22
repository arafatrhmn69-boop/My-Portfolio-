<?php

declare(strict_types=1);

/**
 * Read a single inquiry.
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

Auth::requireLogin();

$inquiryModel = new Inquiry(Database::connection());
$inquiryId = (int) ($_GET['id'] ?? 0);

if ($inquiryId <= 0) {
    abort_request(404, 'Message not found', 'No message identifier was supplied.');
}

$inquiry = $inquiryModel->find($inquiryId);

if ($inquiry === null) {
    abort_request(404, 'Message not found', 'That message may have been deleted already.');
}

$initial = mb_strtoupper(mb_substr(trim((string) $inquiry['sender_name']), 0, 1, 'UTF-8'), 'UTF-8');
$mailto = 'mailto:' . $inquiry['email']
    . '?subject=' . rawurlencode('Re: your message to The Journey Archive');

$pageTitle = 'Inquiry from ' . $inquiry['sender_name'];
$pageHeading = 'Message from ' . $inquiry['sender_name'];
$pageEyebrow = 'Messages / Read';
$pageSubtitle = 'Received ' . local_datetime($inquiry['created_at'], 'j F Y \a\t g:i A') . '.';
$activePage = 'inquiries';

$pageActions = '<a class="btn-line" href="' . e(admin_url('inquiries/index.php')) . '">'
    . '<i class="bi bi-arrow-left" aria-hidden="true"></i> Back to inbox</a>'
    . '<a class="btn-gold" href="' . e($mailto) . '">'
    . '<i class="bi bi-envelope" aria-hidden="true"></i> Reply by email</a>';

require dirname(__DIR__) . '/partials/header.php';
?>

<div class="admin-columns">
    <section class="admin-card" aria-labelledby="message-title">
        <div class="admin-card__head">
            <h2 class="admin-card__title" id="message-title">The <span>message</span></h2>
            <span class="badge-soft badge-soft--gold">Inquiry #<?= e((string) $inquiry['id']) ?></span>
        </div>
        <div class="admin-card__body">
            <div class="inquiry-row" style="margin-bottom:20px">
                <span class="inquiry-avatar" style="width:46px;height:46px;font-size:1rem" aria-hidden="true">
                    <?= e($initial !== '' ? $initial : '?') ?>
                </span>
                <div>
                    <span class="inquiry-name" style="font-size:1rem"><?= e($inquiry['sender_name']) ?></span>
                    <a class="inquiry-email" href="<?= e($mailto) ?>"><?= e($inquiry['email']) ?></a>
                </div>
            </div>

            <div class="message-body"><?= e($inquiry['message']) ?></div>
        </div>
        <div class="admin-card__foot">
            <a class="btn-line btn-sm-admin" href="<?= e($mailto) ?>">
                <i class="bi bi-reply" aria-hidden="true"></i> Reply to <?= e($inquiry['sender_name']) ?>
            </a>
        </div>
    </section>

    <div class="stack">
        <section class="admin-card" aria-labelledby="inquiry-details-title">
            <div class="admin-card__head">
                <h2 class="admin-card__title" id="inquiry-details-title">Sender <span>details</span></h2>
            </div>
            <div class="admin-card__body">
                <dl class="inquiry-meta">
                    <div>
                        <dt>Name</dt>
                        <dd><?= e($inquiry['sender_name']) ?></dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd><a href="<?= e($mailto) ?>"><?= e($inquiry['email']) ?></a></dd>
                    </div>
                    <div>
                        <dt>Received (local time)</dt>
                        <dd><?= e(local_datetime($inquiry['created_at'], 'j M Y, g:i A')) ?></dd>
                    </div>
                    <div>
                        <dt>Stored (UTC)</dt>
                        <dd><?= e(local_datetime($inquiry['created_at'], 'Y-m-d H:i')) ?> UTC</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="admin-card" aria-labelledby="inquiry-actions-title">
            <div class="admin-card__head">
                <h2 class="admin-card__title" id="inquiry-actions-title">Housekeeping</h2>
            </div>
            <div class="admin-card__body">
                <p class="form-hint" style="margin-top:0">
                    Deleting a message removes it permanently from the SQLite database. Archive anything important
                    in your own mailbox first.
                </p>
                <form method="post" action="<?= e(admin_url('inquiries/delete.php')) ?>"
                    data-confirm="Delete the message from <?= e($inquiry['sender_name']) ?>? This cannot be undone.">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="id" value="<?= (int) $inquiry['id'] ?>">
                    <button class="btn-danger-soft btn-sm-admin" type="submit">
                        <i class="bi bi-trash3" aria-hidden="true"></i> Delete this message
                    </button>
                </form>
            </div>
        </section>
    </div>
</div>

<?php require dirname(__DIR__) . '/partials/footer.php'; ?>
