<?php

require_once __DIR__ . "/../config/env.php";
require_once __DIR__ . "/../lib/utils.php";
require_once __DIR__ . "/../lib/hotelsync_client.php";
require_once __DIR__ . "/../lib/logger.php";

function sync_catalog() {

    log_event("INFO", "Starting catalog synchronization");
    $rooms = hs_get_rooms();

    if (!empty($rooms)) {
        process_rooms($rooms);
        log_event("INFO", count($rooms) . " rooms synced.");
    }

    $plans = hs_get_rate_plans();

    if (!empty($plans)) {
        process_rate_plans($plans);
        log_event("INFO", count($plans) . " rate plans synced.");
    }

    log_event("INFO", "Catalog synchronization complete.");
}

function process_rooms($rooms) {
    foreach ($rooms as $room) {

        $code = "HS-" . $room["id_room_types"] . "-" . $room["name"];

        upsert_room(
            $room["id_room_types"],
            $code,
            $room["name"]
        );
    }
}

function process_rate_plans($plans) {
    foreach ($plans as $plan) {

        $code = "RP-" . $plan["id_pricing_plans"] . "-" . $plan["type"];

        upsert_rate_plan(
            $plan["id_pricing_plans"],
            $code,
            $plan["type"]
        );
    }
}
//insert/update by id, but what if its not insert/update by id?
function upsert_room($hs_id, $code, $name) {

    db_upsert(
        "rooms",
        [
            "hs_room_id" => $hs_id,
            "code" => $code,
            "name" => $name
        ],
        "hs_room_id"
    );

}

function upsert_rate_plan($hs_id, $code, $name) {

    db_upsert(
        "rate_plans",
        [
            "hs_rate_plan_id" => $hs_id,
            "code" => $code,
            "meal_plan" => $name
        ],
        "hs_rate_plan_id"
    );
}

?>