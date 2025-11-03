<?php
$mysqli = new mysqli('localhost', 'root', '', 'db_sekatas');
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed', 'detail' => $mysqli->connect_error]);
    exit;
}
$mysqli->set_charset("utf8mb4");
