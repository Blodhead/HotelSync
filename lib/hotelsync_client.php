<?php
function hs_request($endpoint, $method = "GET", $payload = null) {

    $token = HS_TOKEN;

    $ch = curl_init(); //curl handler

    //setting curl options
    curl_setopt($ch, CURLOPT_URL, HS_BASE_URL . $endpoint); //curl endpoint
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //return response as string

    //curl headers definition
    $headers = [
        "Authorization: Bearer $token",
        "Content-Type: application/json",
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //curl headers

    if ($method === "POST") {
        curl_setopt($ch, CURLOPT_POST, true);   //by default curl is set to GET, so we need to set it to POST if that's the method
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload)); //set the payload for POST requests, encoding it as JSON
    }

    $response = curl_exec($ch); //execute the request and store the response

    if (curl_errno($ch)) { //error logging if the request fails
        log_event("api_error", curl_error($ch));
    }

    curl_close($ch); // close the curl handler //depricated in PHP 8.0, but still works for backward compatibility

    return json_decode($response, true); //decode the JSON response into an associative array and return it
}
?>