<?php

require_once __DIR__ . "/../config/env.php";
require_once __DIR__ . "/../lib/utils.php";
require_once __DIR__ . "/../lib/logger.php";

[$payload, $raw_payload] = is_request_ok();
//list($payload, $raw_payload) = is_request_ok(); backwards compatibility

$event_id = $payload['event_id'];
$type = $payload['type'];
$reservation_data = $payload['reservation'];

$payload_hash = hash('sha256', $raw_payload);

is_webhook_event_not_processed($event_id);

db_upsert("webhook_events", [
    "event_id" => $event_id,
    "payload_hash" => $payload_hash,
    "payload" => $raw_payload,
    "status" => "pending"
], null);

//proccess webhook event

function is_request_ok() {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    $raw_payload = file_get_contents('php://input');
    if (!$raw_payload) {
        http_response_code(400);
        echo json_encode(['error' => 'No payload']);
        exit;
    }

    $payload = json_decode($raw_payload, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    //only non generic part of payload is "reservation"
    if (!isset($payload['event_id']) || !isset($payload['type']) || !isset($payload['reservation'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields: event_id, type, reservation']);
        exit;
    }

    return [$payload, $raw_payload];
}

function is_webhook_event_not_processed($event_id) {

    $existing = get_webhook_event_by_id($event_id);
    if (!empty($existing)) {
        http_response_code(200);
        echo json_encode(['status' => 'already_processed']);
        exit;
    }

}

function get_webhook_event_by_id($event_id) {
    return db_select("SELECT id FROM webhook_events WHERE event_id = ?", [$event_id]);
}

?>