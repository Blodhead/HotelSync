<?php

require_once __DIR__ . "/../config/env.php";
require_once __DIR__ . "/../lib/utils.php";
require_once __DIR__ . "/../lib/logger.php";

require_once __DIR__ . "/../lib/hotelsync_client.php";
require_once __DIR__ . "/../services/room_service.php";

function sync_reservations(DateTime $from, DateTime $to) {

    log_event("INFO", "Started reservation synchronization from " . $from->format("Y-m-d") . " to " . $to->format("Y-m-d") . ".");

    $page = 1;

    do {

    $reservations = hs_get_reservations($from, $to, $page);

    if (isset($reservations["reservations"]) && !empty($reservations["reservations"])) {
        process_reservations($reservations["reservations"]);
        log_event("INFO", "Reservation synchronization Page " . $page . " of " . $reservations["total_pages_number"] . " complete.");
    }

    } while ($reservations["total_pages_number"] > $page++);

}

function process_reservations($reservations) {

    foreach ($reservations as $reservation) {

        $lock_id = "LOCK-" . $reservation["id_pricing_plans"] . "-" . $reservation["date_arrival"];    

        $payload_hash = hash("sha256", json_encode($reservation));

        upsert_reservations(
            $reservation["id_reservations"],
            $reservation["first_name"] . " " . $reservation["last_name"],
            $reservation["date_arrival"],
            $reservation["date_departure"],
            $reservation["status"],
            $payload_hash,
            $lock_id
        );

        foreach ($reservation["rooms"] as $room) {
            foreach ($room["nights"] as $night) {
                upsert_reservation_rooms_and_plans(
                    $reservation["id_reservations"],
                    $room["id_rooms"],
                    $night["id_pricing_plans"]
                );  
            }
        }

    }
}

function upsert_reservations($id_reservations, $guest_name, $arrival_date, $departure_date, $status, $payload_hash, $lock_id) {

    db_upsert(
        "reservations",
        [
            "hs_reservation_id" => $id_reservations,
            "guest_name" => $guest_name,
            "arrival_date" => $arrival_date,
            "departure_date" => $departure_date,
            "status" => $status,
            "payload_hash" => $payload_hash,
            "lock_id" => $lock_id
        ],
        "hs_reservation_id"
    );
}

function upsert_reservation_rooms_and_plans($id_reservations, $rate_plan_id, $room_id) {

    db_upsert(
        "reservation_rate_plans",
        [
            "reservation_id" => $id_reservations,
            "rate_plan_id" => $rate_plan_id,
            "hs_room_id" => $room_id
        ],
        "reservation_id"
    );
}

?>