<?php

function log_event($type, $description, $reservation_id = null) {

    $line = date("Y-m-d H:i:s") . " | $type | $description | $reservation_id\n";

    $logFile = dirname(__DIR__) . "/logs/app.log";
    file_put_contents($logFile, $line, FILE_APPEND);
}

?>