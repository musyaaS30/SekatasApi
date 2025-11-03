<?php
function ensureLookup($mysqli, $table, $name) {
    $allowed = ['category', 'type'];
    if (!in_array($table, $allowed)) throw new Exception("Invalid lookup table");

    $stmt = $mysqli->prepare("SELECT id FROM {$table} WHERE name = ? LIMIT 1");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) return intval($row['id']);

    $ins = $mysqli->prepare("INSERT INTO {$table} (name) VALUES (?)");
    $ins->bind_param("s", $name);
    $ins->execute();
    return $ins->insert_id;
}
