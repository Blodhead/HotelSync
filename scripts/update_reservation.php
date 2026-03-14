<?php

require_once __DIR__ . "/../services/reservation_service.php";

$options = getopt("", ["reservation_id:"]);

if (!isset($options["reservation_id"])) {
    echo "Usage: php update_reservation.php --reservation_id=XXXX\n";
    exit(1);
}

update_reservation($options["reservation_id"]);

?>