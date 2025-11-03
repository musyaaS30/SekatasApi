<?php
// helpers/response.php

/**
 * Mengambil body request POST/PUT dalam format JSON dan mengembalikannya sebagai array asosiatif.
 * @return array|null Data request body dalam bentuk array atau null jika body kosong/tidak valid.
 */
function getJsonBody() {
    $body = file_get_contents('php://input');
    // Decode JSON ke array asosiatif (true)
    $data = json_decode($body, true); 
    // Pastikan hasil decode adalah array (data JSON valid)
    return is_array($data) ? $data : null;
}

/**
 * Mengirimkan response JSON yang seragam.
 * @param array|null $data Data yang akan diencode ke JSON
 * @param int $status_code Kode status HTTP (200, 201, 400, 500, dll.)
 */
function respond($data, $status = 200) {
    http_response_code($status);
    // Tambahkan opsi JSON_UNESCAPED_UNICODE seperti yang Anda minta
    if ($data !== null) echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}