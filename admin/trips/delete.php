<?php

declare(strict_types=1);

/**
 * Delete a trip. POST + CSRF only.
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

Auth::requireLogin();

if (!is_post()) {
    if (!headers_sent()) {
        header('Allow: POST');
    }
    abort_request(405, 'Method not allowed', 'Trips can only be deleted from the trip manager.');
}

Csrf::requireValid();

$tripModel = new Trip(Database::connection());
$tripId = (int) ($_POST['id'] ?? 0);
$trip = $tripId > 0 ? $tripModel->find($tripId) : null;

if ($trip === null) {
    Flash::add('warning', 'That trip no longer exists.');
    redirect(admin_url('trips/index.php'));
}

$tripModel->delete($tripId);

// Removes the uploaded file only; seeded photographs in /images are safe.
(new ImageUploader())->delete($trip['image_path']);

Flash::add('success', '“' . $trip['destination_name'] . '” was removed from the archive.');
redirect(admin_url('trips/index.php'));
