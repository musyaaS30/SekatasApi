<?php
function getJsonBody() {
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    return is_array($data) ? $data : null;
}

function respond($data, $status = 200) {
    http_response_code($status);
    if ($data !== null) echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
