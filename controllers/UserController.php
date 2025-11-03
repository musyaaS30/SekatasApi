<?php
// controllers/UserController.php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/response.php'; 

class UserController { // Menggunakan UserController sesuai routes/auth.php
    private $userModel;

    public function __construct() {
        try {
            $this->userModel = new User();
        } catch (Exception $e) {
            // Tangani error koneksi database
            respond(['message' => 'Internal Server Error', 'detail' => 'Koneksi database gagal.'], 500);
            exit; // Hentikan eksekusi jika gagal koneksi
        }
    }

    // --- 1. REGISTER ---
    public function register() {
        $data = getJsonBody();
        
        // 1. Validasi Input Wajib
        if (empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['confirmPassword'])) {
            respond(['message' => 'Nama, email, password, dan konfirmasi password wajib diisi.'], 400);
            return;
        }

        // 2. Validasi Password
        if ($data['password'] !== $data['confirmPassword']) {
            respond(['message' => 'Konfirmasi password tidak cocok.'], 400);
            return;
        }

        // 3. Pengecekan Duplikasi Email
        if ($this->userModel->findByEmail($data['email'])) {
            respond(['message' => 'Gagal mendaftar. Email sudah digunakan.'], 400);
            return;
        }

        // 4. Proses Hashing dan Pembuatan User
        $password_hashed = password_hash($data['password'], PASSWORD_DEFAULT);

        if ($this->userModel->create($data['username'], $data['email'], $password_hashed)) {
            respond(['message' => 'Registrasi berhasil!'], 201); // 201 Created
        } else {
            respond(['message' => 'Gagal menambahkan user ke database.'], 500);
        }
    }
    
    // --- 2. LOGIN ---
    public function login() {
        $data = getJsonBody();

        // 1. Validasi Input
        if (empty($data['email']) || empty($data['password'])) {
            respond(['message' => 'Email dan password wajib diisi.'], 400);
            return;
        }
        
        // 2. Cari User
        $user = $this->userModel->findByEmail($data['email']);

        if ($user && password_verify($data['password'], $user['password'])) {
            
            // Hapus password dari data yang akan dikirim ke client
            unset($user['password']); 

            // Logika Login Berhasil
            respond([
                'message' => 'Login berhasil.',
                // Catatan: Token JWT harusnya dibuat di sini
                'token' => 'dummy_jwt_token', // Placeholder token
                'user' => $user
            ], 200);

        } else {
            // Logika Login Gagal
            respond(['message' => 'Email atau password salah.'], 401); // 401 Unauthorized
        }
    }

    // --- 3. GET ALL USERS (FUNGSI YANG HILANG) ---
    public function getUsers() {
        // PENTING: Dalam aplikasi nyata, endpoint ini harus dilindungi otorisasi Admin!
        
        $users = $this->userModel->getAll();
        
        if (empty($users)) {
            respond(['message' => 'Tidak ada user terdaftar.', 'data' => []], 200);
        }
        
        respond(['message' => 'Data user berhasil diambil.', 'data' => $users], 200);
    }
}