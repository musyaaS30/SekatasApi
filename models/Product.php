<?php
// models/Product.php

require_once __DIR__ . '/../config/database.php';

class Product {
    
    // START FIX: Deklarasikan properti kelas
    private $conn;
    private $table_name = "produk";
    // END FIX: Deklarasikan properti kelas

    public function __construct() {
        // PENTING: Memanggil fungsi getConnection() yang ada di database.php
        // Karena $conn dideklarasikan di atas, ia bisa digunakan oleh $this->conn
        $this->conn = getConnection(); 
        
        if (!$this->conn) {
            throw new Exception("Koneksi database gagal di Model Produk.");
        }
    }

    // --- READ SEMUA PRODUK ---
    public function getAll() {
        // $this->table_name sekarang terdefinisi.
        $query = "SELECT 
                    p.id, p.name, p.brand, p.price, p.image, 
                    c.name AS category_name, 
                    t.name AS type_name,
                    p.created_at, p.updated_at
                  FROM " . $this->table_name . " p
                  JOIN category c ON p.category_id = c.id
                  JOIN type t ON p.type_id = t.id
                  -- Mengurutkan berdasarkan ID, menaik (ASC)
                  ORDER BY p.id ASC"; 
        
        // $this->conn sekarang terdefinisi.
        $result = $this->conn->query($query);
        $products = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        return $products;
    }

    // --- READ SATU PRODUK ---
    public function getById($id) {
        $query = "SELECT 
                    p.id, p.name, p.brand, p.price, p.image, 
                    c.name AS category_name, 
                    t.name AS type_name,
                    p.created_at, p.updated_at
                  FROM " . $this->table_name . " p
                  JOIN category c ON p.category_id = c.id
                  JOIN type t ON p.type_id = t.id
                  WHERE p.id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->num_rows > 0 ? $result->fetch_assoc() : null;
    }

    // --- CREATE PRODUK BARU ---
    public function create($name, $brand, $category_id, $type_id, $price, $image) {
        $query = "INSERT INTO " . $this->table_name . " (name, brand, category_id, type_id, price, image) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        // Tipe data: string, string, integer, integer, integer, string
        $stmt->bind_param("ssiiss", $name, $brand, $category_id, $type_id, $price, $image); 

        return $stmt->execute();
    }

    // --- UPDATE PRODUK ---
    public function update($id, $name, $brand, $category_id, $type_id, $price, $image) {
        // Gunakan IFNULL untuk mengizinkan field tidak diubah jika nilainya null di payload
        $query = "UPDATE " . $this->table_name . " SET 
                    name = IFNULL(?, name), 
                    brand = IFNULL(?, brand), 
                    category_id = IFNULL(?, category_id), 
                    type_id = IFNULL(?, type_id), 
                    price = IFNULL(?, price), 
                    image = IFNULL(?, image)
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);

        // Bind parameter. Semua di set string (s) untuk bind_param
        // Anda mungkin perlu menyesuaikan tipe data binding (i untuk integer, s untuk string)
        $stmt->bind_param("ssiiisi", $name, $brand, $category_id, $type_id, $price, $image, $id);

        return $stmt->execute();
    }

    // --- DELETE PRODUK ---
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }
}