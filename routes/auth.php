<?php
// routes/auth.php

// Memuat UserController dan helper response
require_once __DIR__ . '/../controllers/UserController.php';
// Pastikan file response.php sudah di-require di index.php atau di sini
require_once __DIR__ . '/../helpers/response.php'; 

// Pastikan variabel $segments sudah tersedia dari index.php
// $resource = $segments[0] (misal 'auth'), $action = $segments[1] (misal 'register')
global $segments; // Gunakan global jika Anda tidak memasukkannya sebagai parameter fungsi

$action = $segments[1] ?? null;
$controller = new UserController();
$method = $_SERVER['REQUEST_METHOD'];

switch ($action) {
    case 'register':
        if ($method === 'POST') {
            $controller->register();
        } else {
            respond(['error' => 'Method Not Allowed'], 405);
        }
        break;

    case 'login':
        if ($method === 'POST') {
            $controller->login();
        } else {
            respond(['error' => 'Method Not Allowed'], 405);
        }
        break;

    // --- CASE BARU: GET SEMUA USER ---
    case 'users':
        if ($method === 'GET') {
            // Memanggil fungsi getUsers() yang sudah kita buat di UserController
            $controller->getUsers();
        } else {
            respond(['error' => 'Method Not Allowed'], 405);
        }
        break;

    default:
        // Jika endpoint tidak ditemukan (misal /auth/apa), kembalikan 404
        respond(['error' => 'Auth endpoint not found'], 404);
        break;
}