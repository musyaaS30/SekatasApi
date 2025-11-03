<?php
// index.php

require_once __DIR__ . '/helpers/cors.php';
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/config/database.php'; // Pastikan ini ada

setup_cors(); // Panggil fungsi CORS di awal

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = explode('/', $uri);

// Logika untuk menghapus base path dan mendapatkan segmen URL yang relevan
// Asumsi: Jika diakses via http://localhost/project/index.php/resource/id
$script = basename(__FILE__);
$pos = array_search($script, $parts);

// Jika index.php ditemukan di URL, ambil segmen setelahnya
$segments = $pos !== false ? array_slice($parts, $pos + 1) : $parts;

// Jika tidak ada index.php di URL (pretty URL), cari posisi segmen yang tidak termasuk path dasar
// Ini adalah logika dasar yang mungkin perlu disesuaikan dengan konfigurasi server Anda
$resource = $segments[0] ?? null;
$action = $segments[1] ?? null; // Ambil segmen kedua untuk action (login/register)
$id = isset($segments[2]) ? intval($segments[2]) : null;

switch ($resource) {
    case 'products':
        require_once __DIR__ . '/routes/products.php';
        break;

    // KASUS BARU UNTUK LOGIN/REGISTER
    case 'auth':
        require_once __DIR__ . '/routes/auth.php';
        break;

    default:
        // Jika resource kosong (akses root), bisa diarahkan ke halaman utama atau error
        if ($resource === '') {
             respond(200, ['message' => 'Welcome to Sekatas API']);
        } else {
             respond(404, ['error' => 'Resource not found: ' . $resource]);
        }
}