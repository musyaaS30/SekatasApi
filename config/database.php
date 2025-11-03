<?php
// config/database.php

// Kredensial Database (Sesuai dengan SQL dump dan query Anda)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Password kosong, sesuai dengan contoh new mysqli Anda
define('DB_NAME', 'db_sekatas');

/**
 * Global variable untuk koneksi database.
 * Gunakan ini untuk mencegah inisialisasi koneksi berulang.
 */
$GLOBALS['mysqli'] = null;

/**
 * Fungsi untuk mendapatkan koneksi database global.
 * @return mysqli|null Objek koneksi mysqli atau null jika gagal.
 */
function getConnection() {
    // Gunakan koneksi global jika sudah ada
    if ($GLOBALS['mysqli']) {
        return $GLOBALS['mysqli'];
    }

    // Inisialisasi koneksi baru
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($mysqli->connect_errno) {
        // Karena ini adalah bagian dari API, kita menggunakan helper respond
        // (Asumsi helpers/response.php sudah di-require di index.php)
        if (function_exists('respond')) {
            respond(['error' => 'Database connection failed', 'detail' => $mysqli->connect_error], 500);
        } else {
            // Fallback jika respond() belum di-load (misalnya di models/User.php)
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed', 'detail' => $mysqli->connect_error]);
            exit;
        }
    }

    $mysqli->set_charset("utf8mb4");
    
    // Simpan koneksi ke global dan kembalikan
    $GLOBALS['mysqli'] = $mysqli;
    return $mysqli;
}