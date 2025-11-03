<?php
// helpers/cors.php

/**
 * Mengatur header CORS (Cross-Origin Resource Sharing) sesuai preferensi Anda.
 * Fungsi ini dipanggil dari index.php.
 */
function setup_cors() {
    // Mengatur header CORS
    header("Content-Type: application/json; charset=utf-8");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    // Menangani Preflight Request (OPTIONS)
    // Jika metode request adalah OPTIONS, kirim HTTP 200 OK dan hentikan eksekusi
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}