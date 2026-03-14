<?php

require_once __DIR__ . "/../lib/utils.php";

function sync_rooms($rooms) {

    if (!empty($rooms)) {
        process_rooms($rooms);
        log_event("INFO", count($rooms) . " rooms synced.");
    }
    
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


?>