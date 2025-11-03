<?php
// models/User.php

require_once __DIR__ . '/../config/database.php';

class User {
    // Deklarasi properti kelas (Penting untuk mengatasi Undefined property error)
    private $conn;
    private $table_name = "user";

    public function __construct() {
        $this->conn = getConnection(); 
        
        if (!$this->conn) {
            // Jika koneksi gagal, throw error yang akan ditangkap di Controller
            throw new Exception("Database connection failed.");
        }
    }

    /**
     * Mencari user berdasarkan email.
     * Digunakan untuk proses Login dan pengecekan duplikasi Email saat Register.
     * @param string $email
     * @return array|null Data user (termasuk password) atau null jika tidak ditemukan.
     */
    public function findByEmail($email) {
        $query = "SELECT id, nama, email, password, role FROM " . $this->table_name . " WHERE email = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }

    /**
     * Membuat user baru (Register).
     * @param string $nama
     * @param string $email
     * @param string $password_hashed Password yang sudah di-hash
     * @param string $role Default 'user'
     * @return bool True jika berhasil, False jika gagal.
     */
    public function create($nama, $email, $password_hashed, $role = 'user') {
        $query = "INSERT INTO " . $this->table_name . " (nama, email, password, role, bergabung) VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssss", $nama, $email, $password_hashed, $role);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    /**
     * Mengambil semua data user, digunakan untuk endpoint GET /auth/users.
     * Tidak menyertakan kolom password untuk alasan keamanan.
     * @return array Array berisi daftar user.
     */
    public function getAll() {
        // Mengambil semua kolom kecuali password
        $query = "SELECT id, nama, email, role, bergabung 
                  FROM " . $this->table_name . " 
                  ORDER BY bergabung DESC";
        
        $result = $this->conn->query($query);
        $users = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        return $users;
    }
}