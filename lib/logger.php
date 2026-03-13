<?php

function log_event($type, $description, $reservation_id = null) {

    $line = date("Y-m-d H:i:s") . " | $type | $description | $reservation_id\n";

    file_put_contents("logs/app.log", $line, FILE_APPEND);
}

?>