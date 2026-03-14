<?php

require_once __DIR__ . "/../config/database.php";

/*function db() {
    static $conn;

    if (!$conn) {
        $conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    }

    return $conn;
}*/

function db() {

    // Create connection
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
    }

    return $conn;
}

function update() {
    global $conn;

    $sql = "UPDATE hotels SET name = 'Updated Hotel Name' WHERE id = 1";

    if (mysqli_query($conn, $sql)) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}

function insert() {
    global $conn;

    $sql = "INSERT INTO hotels (name, address) VALUES ('New Hotel', '123 Main St')";

    if (mysqli_query($conn, $sql)) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

function delete() {
    global $conn;

    $sql = "DELETE FROM hotels WHERE id = 1";

    if (mysqli_query($conn, $sql)) {
        echo "Record deleted successfully";
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}

function select() {
    global $conn;

    $sql = "SELECT id, name, address FROM hotels";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        // Output data of each row
        while($row = mysqli_fetch_assoc($result)) {
            echo "id: " . $row["id"]. " - Name: " . $row["name"]. " - Address: " . $row["address"]. "<br>";
        }
    } else {
        echo "0 results";
    }
}


?>