<?php

declare(strict_types=1);

/**
 * Edit an existing trip, optionally replacing or removing its image.
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

Auth::requireLogin();

$tripModel = new Trip(Database::connection());
$uploader = new ImageUploader();

$tripId = (int) ($_GET['id'] ?? 0);

if ($tripId <= 0) {
    abort_request(404, 'Trip not found', 'No trip identifier was supplied.');
}

$trip = $tripModel->find($tripId);

if ($trip === null) {
    abort_request(404, 'Trip not found', 'That journey may have been deleted already.');
}

$form = [
    'destination_name' => (string) $trip['destination_name'],
    'country'          => (string) $trip['country'],
    'description'      => (string) $trip['description'],
    'price_or_budget'  => (string) $trip['price_or_budget'],
    'travel_date'      => (string) $trip['travel_date'],
];
$errors = [];
$currentImage = $trip['image_path'];
$isEdit = true;
$formAction = admin_url('trips/edit.php?id=' . $tripId);

if (is_post()) {
    Csrf::requireValid();
    [$form, $errors] = Trip::validate($_POST);

    $removeImage = isset($_POST['remove_image']) && (string) $_POST['remove_image'] === '1';
    $oldImage = is_string($trip['image_path']) ? $trip['image_path'] : null;
    $newImage = $oldImage;
    $uploadedPath = null;

    if ($errors === []) {
        $uploadError = null;
        $uploadedPath = $uploader->store($_FILES['image'] ?? null, $uploadError);

        if ($uploadError !== null) {
            $errors['image'] = $uploadError;
        } elseif ($uploadedPath !== null) {
            // A new upload always wins over the "remove image" checkbox.
            $newImage = $uploadedPath;
        } elseif ($removeImage) {
            $newImage = null;
        }
    }

    if ($errors === []) {
        try {
            $tripModel->update($tripId, $form, $newImage);
        } catch (Throwable $error) {
            if ($uploadedPath !== null) {
                $uploader->delete($uploadedPath);
            }

            throw $error;
        }

        if ($oldImage !== $newImage && $oldImage !== null) {
            // Only files inside /uploads/trips are removed; seeded photos in
            // /images are intentionally protected by ImageUploader::delete().
            $uploader->delete($oldImage);
        }

        Flash::add('success', 'Changes to “' . $form['destination_name'] . '” were saved.');
        redirect(admin_url('trips/index.php'));
    }

    $currentImage = $newImage;
}

$pageTitle = 'Edit trip';
$pageHeading = 'Edit “' . $trip['destination_name'] . '”';
$pageEyebrow = 'Content / Edit';
$pageSubtitle = 'Update the story, adjust the budget or replace the cover photograph.';
$activePage = 'trips';

$pageActions = '<a class="btn-line" href="' . e(admin_url('trips/index.php')) . '">'
    . '<i class="bi bi-arrow-left" aria-hidden="true"></i> Back to trips</a>'
    . '<a class="btn-line" href="' . e(url('journeys.php')) . '" target="_blank" rel="noopener">'
    . '<i class="bi bi-eye" aria-hidden="true"></i> View live</a>';

require dirname(__DIR__) . '/partials/header.php';
require __DIR__ . '/_form.php';
require dirname(__DIR__) . '/partials/footer.php';
