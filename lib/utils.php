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

function db_select($query, $params = []) {
    $db = db();
    $stmt = mysqli_prepare($db, $query);
    if (!$stmt) {
        log_event("ERROR", "Failed to prepare select statement: " . mysqli_error($db));
        return false;
    }
    if (!empty($params)) {
        $types = str_repeat("s", count($params));
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

?>
