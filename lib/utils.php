<?php

require_once __DIR__ . "/../lib/db.php";

function db_upsert($table, $data, $uniqueKey) {

    $db = db();

    $columns = array_keys($data);
    $values = array_values($data);

    $placeholders = implode(',', array_fill(0, count($columns), '?'));

    $updates = [];
    foreach ($columns as $column) {
        if ($column !== $uniqueKey) {
            $updates[] = "$column = VALUES($column)";
        }
    }

    $updateClause = implode(',', $updates);

    $sql = "INSERT INTO $table (" . implode(',', $columns) . ")
            VALUES ($placeholders)
            ON DUPLICATE KEY UPDATE $updateClause";

    $stmt = mysqli_prepare($db, $sql);

    $types = str_repeat("s", count($values)); // simplified typing

    mysqli_stmt_bind_param($stmt, $types, ...$values);

    mysqli_stmt_execute($stmt);
}

?>