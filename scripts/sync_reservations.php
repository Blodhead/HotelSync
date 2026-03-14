<?php

require_once __DIR__ . "/../services/reservation_service.php";

// Read CLI parameters
$options = getopt("", ["from:", "to:"]);


if (!isset($options["from"]) || !isset($options["to"])) {
    echo "Usage: php sync_reservations.php --from=YYYY-MM-DD --to=YYYY-MM-DD\n";
    exit(1);
}

$from = new DateTime($options["from"]);
$to = new DateTime($options["to"]);

//echo $from->format("Y-m-d") . " to " . $to->format("Y-m-d") . "\n";

sync_reservations($from, $to);