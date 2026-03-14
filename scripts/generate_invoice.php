<?php

require_once __DIR__ . "/../services/invoice_service.php";

$options = getopt("", ["reservation_id:"]);

if (!isset($options["reservation_id"])) {
    echo "Usage: php generate_invoice.php --reservation_id=XXXX\n";
    exit(1);
}

generate_invoice($options["reservation_id"]);

?>