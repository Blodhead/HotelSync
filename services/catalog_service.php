<?php

require_once __DIR__ . "/../config/env.php";
require_once __DIR__ . "/../lib/utils.php";
require_once __DIR__ . "/../lib/logger.php";

require_once __DIR__ . "/../lib/hotelsync_client.php";
require_once __DIR__ . "/../services/room_service.php";
require_once __DIR__ . "/../services/rate_service.php";


function sync_catalog() {

    log_event("INFO", "Starting catalog synchronization");

    $rooms = hs_get_rooms();

    sync_rooms($rooms);

    $plans = hs_get_rate_plans();

    sync_rate_plans($plans);

    log_event("INFO", "Catalog synchronization complete.");
}


?>