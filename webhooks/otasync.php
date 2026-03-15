<?php

require_once __DIR__ . "/../config/env.php";
require_once __DIR__ . "/../lib/utils.php";
require_once __DIR__ . "/../lib/logger.php";
require_once __DIR__ . "/../services/reservation_service.php";

[$payload, $raw_payload] = is_request_ok();
//list($payload, $raw_payload) = is_request_ok(); backwards compatibility

$event_id = $payload['event_id'];
$type = $payload['type'];
$reservation_data = $payload['reservation'];

$payload_hash = hash('sha256', $raw_payload);

is_webhook_event_not_processed($event_id);

upsert_webhook_event($event_id, "pending", null, $payload_hash, $raw_payload);

try {

    process_webhook_reservation_event($type, $reservation_data);

    upsert_webhook_event($event_id, "processed", date('Y-m-d H:i:s'));

    log_event("INFO", "Webhook event $event_id processed successfully");
    http_response_code(200);
    echo json_encode(['status' => 'processed']);

} catch (Exception $e) {

    upsert_webhook_event($event_id, "failed");

    log_event("ERROR", "Failed to process webhook event $event_id: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Processing failed']);

}


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

function upsert_webhook_event($event_id, $status, $processed_at = null, $payload_hash = null, $payload = null) {
    $data = ["event_id" => $event_id, "status" => $status];
    if ($processed_at) $data["processed_at"] = $processed_at;
    if ($payload_hash) $data["payload_hash"] = $payload_hash;
    if ($payload) $data["payload"] = $payload;
    $unique_key = $payload_hash ? null : "event_id";
    db_upsert("webhook_events", $data, $unique_key);
}

function process_webhook_reservation_event($type, $reservation) {

    $reservation_id = $reservation['id_reservations'];

    upsert_reservations($reservation_id, 
    ($reservation['first_name'] ?? '') . ' ' . ($reservation['last_name'] ?? ''),
    $reservation['date_arrival'] ?? null,
    $reservation['date_departure'] ?? null,
    $reservation['status'] ?? 'confirmed',
    hash('sha256', json_encode($reservation)),
    "LOCK-" . ($reservation['id_pricing_plans'] ?? '') . "-" . ($reservation['date_arrival'] ?? '')
    );

    if ($type === 'new') {

        db_upsert("audit_log", [
            "reservation_id" => $reservation_id,
            "action" => "webhook_new",
            "details" => "New reservation via webhook"
        ], null);

    } elseif ($type === 'update') {

        db_upsert("audit_log", [
            "reservation_id" => $reservation_id,
            "action" => "webhook_update",
            "details" => "Reservation updated via webhook"
        ], null);

    } elseif ($type === 'cancel') {

        db_upsert("audit_log", [
            "reservation_id" => $reservation_id,
            "action" => "webhook_cancel",
            "details" => "Reservation canceled via webhook"
        ], null);
        
    } else {
        throw new Exception("Unknown event type: $type");
    }
}

?>