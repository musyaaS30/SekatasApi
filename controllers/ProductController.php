<?php
// controllers/ProductController.php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../helpers/response.php'; // Mengandung respond() dan getJsonBody()

class ProductController {
    private $productModel;

    public function __construct() {
        try {
            $this->productModel = new Product();
        } catch (Exception $e) {
            respond(['message' => 'Internal Server Error', 'detail' => $e->getMessage()], 500);
        }
    }

    // --- GET SEMUA PRODUK (C-R-U-D) ---
    public function index() {
        $products = $this->productModel->getAll();
        if (empty($products)) {
            respond(['message' => 'Tidak ada produk ditemukan.', 'data' => []], 200);
        }
        respond(['message' => 'Data produk berhasil diambil.', 'data' => $products], 200);
    }

    // --- GET PRODUK BERDASARKAN ID (C-R-U-D) ---
    public function show($id) {
        if (!$id) {
            respond(['message' => 'ID Produk wajib diisi.'], 400);
        }
        $product = $this->productModel->getById($id);
        if ($product) {
            respond(['message' => 'Detail produk berhasil diambil.', 'data' => $product], 200);
        } else {
            respond(['message' => 'Produk tidak ditemukan.'], 404);
        }
    }

    // --- TAMBAH PRODUK BARU (C-R-U-D) ---
    public function store() {
        $data = getJsonBody();

        // Validasi Wajib
        if (empty($data['name']) || empty($data['brand']) || empty($data['price'])) {
            respond(['message' => 'Nama, brand, dan harga wajib diisi.'], 400);
        }
        // Validasi ID (asumsi category_id/type_id adalah integer, default ke 1 jika kosong)
        $category_id = intval($data['category_id'] ?? 1); 
        $type_id = intval($data['type_id'] ?? 1); 

        if ($this->productModel->create(
            $data['name'], 
            $data['brand'], 
            $category_id, 
            $type_id, 
            intval($data['price']), 
            $data['image'] ?? null
        )) {
            respond(['message' => 'Produk berhasil ditambahkan.'], 201);
        } else {
            respond(['message' => 'Gagal menambahkan produk. Cek input atau koneksi DB.'], 500);
        }
    }

    // --- UPDATE PRODUK (C-R-U-D) ---
    public function update($id) {
        if (!$id) {
            respond(['message' => 'ID Produk wajib diisi.'], 400);
        }
        
        $data = getJsonBody();
        // Cek apakah ada data yang dikirim
        if (!$data) {
             respond(['message' => 'Tidak ada data untuk diperbarui.'], 400);
        }

        // Ambil nilai, gunakan null jika tidak ada di payload (untuk fungsi IFNULL di Model)
        $name = $data['name'] ?? null;
        $brand = $data['brand'] ?? null;
        $category_id = isset($data['category_id']) ? intval($data['category_id']) : null;
        $type_id = isset($data['type_id']) ? intval($data['type_id']) : null;
        $price = isset($data['price']) ? intval($data['price']) : null;
        $image = $data['image'] ?? null;

        if ($this->productModel->update($id, $name, $brand, $category_id, $type_id, $price, $image)) {
             // Cek apakah produk benar-benar ada sebelum merespon sukses
             if (!$this->productModel->getById($id)) {
                 respond(['message' => 'Produk tidak ditemukan, gagal update.'], 404);
             }
            respond(['message' => 'Produk berhasil diperbarui.'], 200);
        } else {
            respond(['message' => 'Gagal memperbarui produk. Cek ID dan format data.'], 500);
        }
    }

    // --- HAPUS PRODUK (C-R-U-D) ---
    public function destroy($id) {
        if (!$id) {
            respond(['message' => 'ID Produk wajib diisi.'], 400);
        }
        
        if (!$this->productModel->getById($id)) {
             respond(['message' => 'Produk tidak ditemukan.'], 404);
        }

        if ($this->productModel->delete($id)) {
            respond(['message' => 'Produk berhasil dihapus.'], 200);
        } else {
            respond(['message' => 'Gagal menghapus produk.'], 500);
        }
    }
}