<?php

declare(strict_types=1);

/**
 * Shared trip form (used by create.php and edit.php).
 *
 * Expected variables:
 *   $form         array  Current (or submitted) field values
 *   $errors       array  field => message
 *   $currentImage ?string stored image path for an existing trip
 *   $isEdit       bool
 *   $formAction   string POST target
 */

if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

$currentImage = $currentImage ?? null;
$isEdit = $isEdit ?? false;
$hasImage = is_string($currentImage) && $currentImage !== '';
$uploadError = $errors['image'] ?? '';
?>
<form method="post" action="<?= e($formAction) ?>" enctype="multipart/form-data" data-submit-once>
    <?= Csrf::field() ?>

    <div class="admin-columns">
        <div class="stack">
            <section class="admin-card" aria-labelledby="trip-details-title">
                <div class="admin-card__head">
                    <h2 class="admin-card__title" id="trip-details-title">Trip <span>details</span></h2>
                    <span class="badge-soft"><?= $isEdit ? 'Editing' : 'New entry' ?></span>
                </div>
                <div class="admin-card__body">
                    <div class="form-grid">
                        <div class="form-field">
                            <label class="form-label-admin" for="destination_name">
                                Destination name <span class="req" aria-hidden="true">*</span>
                            </label>
                            <input class="form-control-admin<?= isset($errors['destination_name']) ? ' is-invalid-admin' : '' ?>"
                                type="text" id="destination_name" name="destination_name"
                                value="<?= e($form['destination_name']) ?>" maxlength="120" required
                                placeholder="Cox's Bazar"
                                <?= isset($errors['destination_name']) ? 'aria-invalid="true" aria-describedby="error-destination_name"' : '' ?>>
                            <?php if (isset($errors['destination_name'])): ?>
                                <span class="form-error" id="error-destination_name"><?= e($errors['destination_name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-field">
                            <label class="form-label-admin" for="country">
                                Country <span class="req" aria-hidden="true">*</span>
                            </label>
                            <input class="form-control-admin<?= isset($errors['country']) ? ' is-invalid-admin' : '' ?>"
                                type="text" id="country" name="country" value="<?= e($form['country']) ?>"
                                maxlength="80" required placeholder="Bangladesh"
                                <?= isset($errors['country']) ? 'aria-invalid="true" aria-describedby="error-country"' : '' ?>>
                            <?php if (isset($errors['country'])): ?>
                                <span class="form-error" id="error-country"><?= e($errors['country']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-field">
                            <label class="form-label-admin" for="travel_date">
                                Travel date <span class="req" aria-hidden="true">*</span>
                            </label>
                            <input class="form-control-admin<?= isset($errors['travel_date']) ? ' is-invalid-admin' : '' ?>"
                                type="date" id="travel_date" name="travel_date" value="<?= e($form['travel_date']) ?>"
                                min="1900-01-01" max="2100-12-31" required
                                <?= isset($errors['travel_date']) ? 'aria-invalid="true" aria-describedby="error-travel_date"' : '' ?>>
                            <?php if (isset($errors['travel_date'])): ?>
                                <span class="form-error" id="error-travel_date"><?= e($errors['travel_date']) ?></span>
                            <?php else: ?>
                                <p class="form-hint">The journey is sorted by this date.</p>
                            <?php endif; ?>
                        </div>

                        <div class="form-field">
                            <label class="form-label-admin" for="price_or_budget">Price or budget</label>
                            <input class="form-control-admin<?= isset($errors['price_or_budget']) ? ' is-invalid-admin' : '' ?>"
                                type="text" id="price_or_budget" name="price_or_budget"
                                value="<?= e($form['price_or_budget']) ?>" maxlength="60"
                                placeholder="BDT 8,500 / person"
                                <?= isset($errors['price_or_budget']) ? 'aria-invalid="true" aria-describedby="error-price_or_budget"' : '' ?>>
                            <?php if (isset($errors['price_or_budget'])): ?>
                                <span class="form-error" id="error-price_or_budget"><?= e($errors['price_or_budget']) ?></span>
                            <?php else: ?>
                                <p class="form-hint">Free text — a price, a range, or “Budget trip”.</p>
                            <?php endif; ?>
                        </div>

                        <div class="form-field form-field--full">
                            <label class="form-label-admin" for="description">
                                Travel story / description <span class="req" aria-hidden="true">*</span>
                            </label>
                            <textarea class="form-control-admin<?= isset($errors['description']) ? ' is-invalid-admin' : '' ?>"
                                id="description" name="description" rows="8" maxlength="5000" required
                                placeholder="What made this place stay with you?"
                                <?= isset($errors['description']) ? 'aria-invalid="true" aria-describedby="error-description"' : '' ?>><?= e($form['description']) ?></textarea>
                            <?php if (isset($errors['description'])): ?>
                                <span class="form-error" id="error-description"><?= e($errors['description']) ?></span>
                            <?php endif; ?>
                            <div class="char-counter" data-counter-for="description" data-counter-max="5000">
                                <?= e((string) mb_strlen($form['description'], 'UTF-8')) ?> / 5000
                            </div>
                        </div>
                    </div>
                </div>
                <div class="admin-card__foot">
                    <div class="form-actions">
                        <button class="btn-gold" type="submit">
                            <i class="bi bi-check2" aria-hidden="true"></i>
                            <?= $isEdit ? 'Save changes' : 'Publish trip' ?>
                        </button>
                        <a class="btn-line" href="<?= e(admin_url('trips/index.php')) ?>">Cancel</a>
                        <span class="form-actions__spacer"></span>
                        <span class="form-hint" style="margin:0">
                            <span aria-hidden="true">*</span> Required fields
                        </span>
                    </div>
                </div>
            </section>
        </div>

        <div class="stack">
            <section class="admin-card" aria-labelledby="trip-image-title">
                <div class="admin-card__head">
                    <h2 class="admin-card__title" id="trip-image-title">Cover <span>photograph</span></h2>
                </div>
                <div class="admin-card__body">
                    <div class="image-preview" id="imagePreview">
                        <?php if ($hasImage): ?>
                            <img src="<?= e(image_url($currentImage)) ?>" alt="Current cover photograph for this trip"
                                id="imagePreviewImg">
                        <?php else: ?>
                            <img src="<?= e(image_url(null)) ?>" alt="Placeholder — no photograph selected"
                                id="imagePreviewImg" hidden>
                            <div class="image-preview__empty">
                                No photograph yet<br>
                                <span>the placeholder will be used</span>
                            </div>
                        <?php endif; ?>
                        <span class="image-preview__badge">
                            <?= $hasImage ? 'Current image' : 'Placeholder' ?>
                        </span>
                    </div>

                    <div class="upload-box" style="margin-top:14px">
                        <label class="form-label-admin" for="image" style="margin:0">
                            <?= $isEdit ? 'Replace photograph' : 'Upload photograph' ?>
                        </label>
                        <input type="file" id="image" name="image"
                            accept="image/jpeg,image/png,image/webp" data-image-input="imagePreview"
                            <?= $uploadError !== '' ? 'aria-invalid="true" aria-describedby="error-image"' : '' ?>>
                        <p class="form-hint" style="margin:0">
                            JPG, PNG or WebP · maximum 5 MB · landscape images around 1600 × 1000 look best.
                        </p>
                        <?php if ($uploadError !== ''): ?>
                            <span class="form-error" id="error-image"><?= e($uploadError) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($isEdit && $hasImage): ?>
                        <div class="check-row" style="margin-top:14px">
                            <input type="checkbox" id="remove_image" name="remove_image" value="1">
                            <label for="remove_image">
                                <strong>Remove the current photograph</strong>
                                The trip keeps its details and falls back to the archive placeholder.
                            </label>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="admin-card" aria-labelledby="trip-publish-title">
                <div class="admin-card__head">
                    <h2 class="admin-card__title" id="trip-publish-title">Publishing <span>notes</span></h2>
                </div>
                <div class="admin-card__body">
                    <ul class="security-list">
                        <li>
                            <i class="bi bi-shield-check" aria-hidden="true"></i>
                            <span>Uploads are MIME-checked, renamed randomly and stored where PHP cannot execute.</span>
                        </li>
                        <li>
                            <i class="bi bi-globe2" aria-hidden="true"></i>
                            <span>Published trips appear immediately on the public journeys page and JSON feed.</span>
                        </li>
                        <li>
                            <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                            <span>Replacing or removing an image deletes the old file from <code>/uploads/trips</code>.</span>
                        </li>
                    </ul>
                </div>
            </section>
        </div>
    </div>
</form>
