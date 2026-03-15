<?php

require_once __DIR__ . "/../services/reservation_service.php";

$options = getopt("", ["from:", "to:"]);


if (!isset($options["from"]) || !isset($options["to"])) {
    echo "Usage: php sync_reservations.php --from=YYYY-MM-DD --to=YYYY-MM-DD\n";
    exit(1);
}

$from = new DateTime($options["from"]);
$to = new DateTime($options["to"]);


sync_reservations($from, $to);