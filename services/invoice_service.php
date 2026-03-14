<?php

require_once __DIR__ . "/../config/env.php";
require_once __DIR__ . "/../lib/utils.php";
require_once __DIR__ . "/../lib/logger.php";

require_once __DIR__ . "/../services/reservation_service.php";

function generate_invoice($reservation_id) {
    log_event("INFO", "Starting invoice generation for reservation $reservation_id");

    // Fetch reservation from DB
    $local = fetch_db_reservation($reservation_id);

    if (empty($local)) {
        log_event("ERROR", "Reservation $reservation_id not found");
        echo "Reservation not found\n";
        return;
    }

    process_invoice($local[0]);

}

function process_invoice($reservation) {

    $reservation_id = $reservation["hs_reservation_id"];
    $year = date('Y');

    $new_number = process_invoice_queue($reservation, $year);

    //$invoice_number = "HS-INV-" . $year . "-" . str_pad($new_number, 6, '0', STR_PAD_LEFT);
    $invoice_number = sprintf("HS-INV-%d-%06d", $year, $new_number);

    $payload = [
        "services" => [
            [
                "name" => "Room Accommodation",
                "quantity" => 1,
                "price_per_unit" => 100.00,
                "discount_amount" => 0,
                "discount_type" => "percent",
                "tax" => "0%",
                "type" => "room"
            ]
        ],
        "guest_data" => [
            "first_name" => $reservation["guest_name"] ? explode(" ", $reservation["guest_name"])[0] : "",
            "last_name" => $reservation["guest_name"] ? explode(" ", $reservation["guest_name"])[1] : ""
        ],
        "id_reservations" => $reservation_id,
        "price_total" => "100.00",
        "id_properties" => PROPERTY_ID,
        "date_delivered" => date("Y-m-d"),
        "date_issued" => date("Y-m-d"),
        "type" => "invoice",
        "invoice_number" => $invoice_number
    ];

    
    db_upsert("invoice_queue", [
        "reservation_id" => $reservation_id,
        "invoice_number" => $invoice_number,
        "payload" => json_encode($payload),
        "status" => "pending",
        "retry_count" => 0
    ], null);

    log_event("INFO", "Invoice $invoice_number generated for reservation $reservation_id");

}

function process_invoice_queue($reservation, $year) {

    $db = db();
    
    mysqli_begin_transaction($db);

    $counter = fetch_db_invoice_counter();

    if (empty($counter)) {
        db_upsert("invoice_counters", ["year" => $year, "last_number" => 0], "year");
        $last_number = 0;
    } else {
        $last_number = $counter[0]["last_number"];
    }

    $new_number = $last_number + 1;
    db_upsert("invoice_counters", ["year" => $year, "last_number" => $new_number], "year");

    mysqli_commit($db);

    return $new_number;

}

function fetch_db_invoice_counter() {

    return db_select("SELECT last_number FROM invoice_counters WHERE year = ? FOR UPDATE", [date('Y')]);
    
}

?>