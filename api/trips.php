<?php

declare(strict_types=1);

/**
 * Public JSON feed of trips.
 *
 *   GET /api/trips.php            → up to 100 trips
 *   GET /api/trips.php?limit=6    → up to 6 trips (1–200)
 *
 * The response contains pre-escaped-free raw text plus a ready-to-use
 * image_url. Consumers must still escape values when writing HTML.
 */

define('APP_NO_SESSION', true);

require_once dirname(__DIR__) . '/config/bootstrap.php';

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    if (!headers_sent()) {
        header('Allow: GET');
    }
    json_response(['ok' => false, 'message' => 'Only GET requests are supported.'], 405);
}

$limit = (int) ($_GET['limit'] ?? 100);
$limit = max(1, min($limit, 200));

$trips = (new Trip(Database::connection()))->all($limit);

$data = array_map(static function (array $trip): array {
    return [
        'id'               => (int) $trip['id'],
        'destination_name' => (string) $trip['destination_name'],
        'country'          => (string) $trip['country'],
        'description'      => (string) $trip['description'],
        'price_or_budget'  => (string) $trip['price_or_budget'],
        'travel_date'      => (string) $trip['travel_date'],
        'image_path'       => $trip['image_path'] !== null ? (string) $trip['image_path'] : null,
        'image_url'        => image_url($trip['image_path']),
        'created_at'       => (string) $trip['created_at'],
    ];
}, $trips);

if (!headers_sent()) {
    header('Cache-Control: public, max-age=60');
}

json_response([
    'ok'    => true,
    'count' => count($data),
    'data'  => $data,
]);
