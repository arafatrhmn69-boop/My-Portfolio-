<?php

declare(strict_types=1);

/**
 * Delete an inquiry. POST + CSRF only.
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

Auth::requireLogin();

if (!is_post()) {
    if (!headers_sent()) {
        header('Allow: POST');
    }
    abort_request(405, 'Method not allowed', 'Messages can only be deleted from the inquiry manager.');
}

Csrf::requireValid();

$inquiryModel = new Inquiry(Database::connection());
$inquiryId = (int) ($_POST['id'] ?? 0);
$inquiry = $inquiryId > 0 ? $inquiryModel->find($inquiryId) : null;

if ($inquiry === null) {
    Flash::add('warning', 'That message no longer exists.');
    redirect(admin_url('inquiries/index.php'));
}

$inquiryModel->delete($inquiryId);
Flash::add('success', 'The message from ' . $inquiry['sender_name'] . ' was deleted.');
redirect(admin_url('inquiries/index.php'));
