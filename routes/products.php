<?php
// routes/products.php

require_once __DIR__ . '/../controllers/ProductController.php';

// Variabel $action (ID) berasal dari index.php
$id = $action; // Menggunakan variabel $action dari index.php sebagai ID
$controller = new ProductController();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if ($id) {
            // GET /products/123 -> Detail Produk
            $controller->show($id);
        } else {
            // GET /products -> Daftar Semua Produk
            $controller->index();
        }
        break;

    case 'POST':
        // POST /products -> Tambah Produk Baru
        $controller->store();
        break;

    case 'PUT':
        // PUT /products/123 -> Update Produk
        $controller->update($id);
        break;

    case 'DELETE':
        // DELETE /products/123 -> Hapus Produk
        $controller->destroy($id);
        break;

    default:
        respond(['error' => 'Method Not Allowed'], 405);
        break;
}