<?php

declare(strict_types=1);

/**
 * Create a trip, with an optional cover photograph.
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

Auth::requireLogin();

$pdo = Database::connection();
$tripModel = new Trip($pdo);
$uploader = new ImageUploader();

$form = [
    'destination_name' => '',
    'country'          => '',
    'description'      => '',
    'price_or_budget'  => '',
    'travel_date'      => '',
];
$errors = [];
$currentImage = null;
$isEdit = false;
$formAction = admin_url('trips/create.php');

if (is_post()) {
    Csrf::requireValid();
    [$form, $errors] = Trip::validate($_POST);

    $uploadedPath = null;

    // Only touch the filesystem when the text fields are already valid, so a
    // failed form never leaves an orphaned image behind.
    if ($errors === []) {
        $uploadError = null;
        $uploadedPath = $uploader->store($_FILES['image'] ?? null, $uploadError);

        if ($uploadError !== null) {
            $errors['image'] = $uploadError;
        }
    }

    if ($errors === []) {
        try {
            $tripModel->create($form, $uploadedPath);
        } catch (Throwable $error) {
            if ($uploadedPath !== null) {
                $uploader->delete($uploadedPath);
            }

            throw $error;
        }

        Flash::add('success', '“' . $form['destination_name'] . '” was added to the archive.');
        redirect(admin_url('trips/index.php'));
    }
}

$pageTitle = 'Add trip';
$pageHeading = 'Add a new trip';
$pageEyebrow = 'Content / New';
$pageSubtitle = 'Give the place a name, a date and the story you want to remember. A cover photograph can be added now or later.';
$activePage = 'trips';

$pageActions = '<a class="btn-line" href="' . e(admin_url('trips/index.php')) . '">'
    . '<i class="bi bi-arrow-left" aria-hidden="true"></i> Back to trips</a>';

require dirname(__DIR__) . '/partials/header.php';
require __DIR__ . '/_form.php';
require dirname(__DIR__) . '/partials/footer.php';
