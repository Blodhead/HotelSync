<?php


function hs_request($endpoint, $method = "GET", $payload = null) {

    $token = HS_API_TOKEN;

    $ch = curl_init(); //curl handler

    //curl headers definition
    $headers = [
        "Authorization: Bearer $token",
        "Content-Type: application/json",
    ];
    
    //common curl options for all requests
    $options = [
        CURLOPT_URL => HS_BASE_URL . $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30
    ];

    //setting curl options based on the request method
    if ($method === "POST") {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = json_encode($payload);
    } elseif ($method === "PUT") {
        $options[CURLOPT_CUSTOMREQUEST] = "PUT";
        $options[CURLOPT_POSTFIELDS] = json_encode($payload);
    } elseif ($method === "GET" && $payload) {
        $options[CURLOPT_URL] .= '?' . http_build_query($payload);
    } elseif ($method === "DELETE") {
        $options[CURLOPT_CUSTOMREQUEST] = "DELETE";
        $options[CURLOPT_POSTFIELDS] = json_encode($payload);
    }

    //setting curl options
    curl_setopt_array($ch, $options); //set multiple curl options at once


    $response = curl_exec($ch); //execute the request and store the response

    if (curl_errno($ch)) { //error logging if the request fails
        log_event("api_error", curl_error($ch));
    }

    curl_close($ch); // close the curl handler //depricated in PHP 8.0, but still works for backward compatibility

    return json_decode($response, true); //decode the JSON response into an associative array and return it
}

//if id_properties, token and key keep repeating, we can create helper functions to make it cleaner and more reusable

function hs_get_rooms() {
    return hs_request("room/data/rooms", "POST", [
        "id_properties" => PROPERTY_ID,
        "token" => HS_API_TOKEN,
        "key" => HS_API_KEY,
        "type"=> 1,
        "details"=> "0"
    ]);
}

function hs_get_rate_plans() {
    return hs_request("pricingPlan/data/pricing_plans", "POST", [
        "id_properties" => PROPERTY_ID,
        "key" => HS_API_KEY,
        "token" => HS_API_TOKEN
    ]);
}

function hs_get_reservations(DateTime $from, DateTime $to, int $page = 1) {
    return hs_request("reservation/data/reservations", "POST", [
        "id_properties" => PROPERTY_ID,
        "key" => HS_API_KEY,
        "token" => HS_API_TOKEN,
        "channels"=> [],
        "countries"=> [],
        "order_by"=> "date_received",
        "rooms"=> [],
        "dfrom"=> $from->format('Y-m-d'),
        "dto"=> $to->format('Y-m-d'),
        "filter_by"=> "date_received",
        "order_type"=> "desc",
        "page"=> $page,
        "show_nights"=> 1,
        "show_rooms"=> 1,
        "view_type"=> "reservations"
    ]);
}

?>