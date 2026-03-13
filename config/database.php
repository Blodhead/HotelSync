<?php
$DB_HOST = "localhost";
$DB_USER = "BridgeOneTest";
$DB_PASS = "Bvp66m@S6ro2";
$DB_NAME = "BridgeOneDB";

// Create connection
$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";

/*function db() {
    static $conn;

    if (!$conn) {
        $conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    }

    return $conn;
}*/
?>