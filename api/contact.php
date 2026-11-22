<?php

declare(strict_types=1);

/**
 * Public contact-form endpoint.
 *
 * GET  → { csrf_token, server_time }  (call this before showing the form)
 * POST → stores a message in the `inquiries` table
 *
 * Accepted POST bodies:
 *   - application/json  (preferred; send X-CSRF-Token header or _csrf field)
 *   - application/x-www-form-urlencoded or multipart/form-data
 *
 * Protections: CSRF token, honeypot field, minimum fill time, per-session
 * rate limit, strict validation, PDO prepared statements.
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

if (!headers_sent()) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
    json_response([
        'ok'          => true,
        'csrf_token'  => Csrf::token(),
        'server_time' => time(),
    ]);
}

if ($method !== 'POST') {
    if (!headers_sent()) {
        header('Allow: GET, POST');
    }
    json_response(['ok' => false, 'message' => 'Only GET and POST requests are supported.'], 405);
}

// ---------------------------------------------------------------------------
//  Parse input
// ---------------------------------------------------------------------------
$contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
$input = $_POST;

if (str_contains($contentType, 'application/json')) {
    $raw = file_get_contents('php://input');
    $decoded = json_decode(is_string($raw) ? $raw : '', true);

    if (!is_array($decoded)) {
        json_response(['ok' => false, 'message' => 'The request body is not valid JSON.'], 400);
    }

    $input = $decoded;
}

$token = $input['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

if (!Csrf::validate(is_string($token) ? $token : null)) {
    json_response([
        'ok'      => false,
        'message' => 'Your form session expired. Please reload the page and try again.',
        'errors'  => ['form' => 'Invalid or missing security token.'],
    ], 419);
}

// ---------------------------------------------------------------------------
//  Bot traps
// ---------------------------------------------------------------------------
// A hidden "website" field must stay empty. Bots fill it in; humans never see it.
if (is_string($input['website'] ?? null) && trim((string) $input['website']) !== '') {
    json_response([
        'ok'      => true,
        'message' => 'Thank you. Your message has been received.',
    ], 201);
}

if (isset($input['started_at']) && is_numeric($input['started_at'])) {
    $elapsed = time() - (int) $input['started_at'];

    if ($elapsed >= 0 && $elapsed < CONTACT_MIN_SECONDS) {
        json_response([
            'ok'      => false,
            'message' => 'That was a little too quick — please try submitting again.',
            'errors'  => ['form' => 'Submitted too quickly.'],
        ], 422);
    }
}

// ---------------------------------------------------------------------------
//  Per-session rate limit
// ---------------------------------------------------------------------------
$now = time();
$recent = $_SESSION['contact_recent'] ?? [];
$recent = is_array($recent)
    ? array_values(array_filter($recent, static fn ($stamp): bool => is_int($stamp) && $stamp > $now - 3600))
    : [];

if (count($recent) >= CONTACT_MAX_PER_HOUR) {
    json_response([
        'ok'      => false,
        'message' => 'You have sent several messages already. Please try again later.',
        'errors'  => ['form' => 'Rate limit reached.'],
    ], 429);
}

// ---------------------------------------------------------------------------
//  Validation
// ---------------------------------------------------------------------------
$senderName = is_scalar($input['sender_name'] ?? null) ? trim((string) $input['sender_name']) : '';
$email = is_scalar($input['email'] ?? null) ? trim((string) $input['email']) : '';
$message = is_scalar($input['message'] ?? null) ? trim((string) $input['message']) : '';
$message = str_replace(["\r\n", "\r"], "\n", $message);

$validator = (new Validator([
    'sender_name' => $senderName,
    'email'       => $email,
    'message'     => $message,
]))
    ->required('sender_name', 'Your name')
    ->maxLength('sender_name', 120, 'Your name')
    ->required('email', 'Email address')
    ->maxLength('email', 190, 'Email address')
    ->email('email', 'Email address')
    ->required('message', 'Message')
    ->minLength('message', 10, 'Message')
    ->maxLength('message', 5000, 'Message');

if ($validator->fails()) {
    json_response([
        'ok'      => false,
        'message' => 'Please check the highlighted fields and try again.',
        'errors'  => $validator->errors(),
    ], 422);
}

// ---------------------------------------------------------------------------
//  Store
// ---------------------------------------------------------------------------
$inquiryModel = new Inquiry(Database::connection());
$inquiryModel->create([
    'sender_name' => $senderName,
    'email'       => $email,
    'message'     => $message,
]);

$recent[] = $now;
$_SESSION['contact_recent'] = array_slice($recent, -CONTACT_MAX_PER_HOUR);

json_response([
    'ok'      => true,
    'message' => 'Thank you, ' . $senderName . '. Your message is in the archive.',
], 201);
