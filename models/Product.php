<?php
require_once __DIR__ . '/Lookup.php';

class Product
{
    private $db;

    public function __construct($mysqli)
    {
        $this->db = $mysqli;
    }

    /** Ambil semua produk */
    public function getAll()
    {
        $sql = "SELECT p.id, p.name, p.brand, c.name AS category, t.name AS `type`, p.price, p.image
                FROM produk p
                LEFT JOIN category c ON p.category_id = c.id
                LEFT JOIN type t ON p.type_id = t.id
                ORDER BY p.id ASC";
        $res = $this->db->query($sql);
        return $res->fetch_all(MYSQLI_ASSOC);
    }

    /** Ambil produk berdasarkan ID */
    public function getById($id)
    {
        $sql = "SELECT p.id, p.name, p.brand, c.name AS category, t.name AS `type`, p.price, p.image
                FROM produk p
                LEFT JOIN category c ON p.category_id = c.id
                LEFT JOIN type t ON p.type_id = t.id
                WHERE p.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /** Buat produk baru */
    public function create($data)
    {
        $catId = ensureLookup($this->db, 'category', $data['category']);
        $typeId = ensureLookup($this->db, 'type', $data['type']);

        $sql = "INSERT INTO produk (name, brand, category_id, type_id, price, image)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssiiis", $data['name'], $data['brand'], $catId, $typeId, $data['price'], $data['image']);
        $stmt->execute();

        return $this->getById($stmt->insert_id);
    }

    /** Update produk */
    public function update($id, $data)
    {
        // Ambil data existing
        $existing = $this->getById($id);
        if (!$existing) return false;

        // Gunakan variabel untuk semua field agar bind_param bisa menerima by reference
        $name     = isset($data['name']) ? $data['name'] : $existing['name'];
        $brand    = isset($data['brand']) ? $data['brand'] : $existing['brand'];
        $category = isset($data['category']) ? $data['category'] : $existing['category'];
        $type     = isset($data['type']) ? $data['type'] : $existing['type'];
        $price    = isset($data['price']) ? $data['price'] : $existing['price'];
        $image    = isset($data['image']) ? $data['image'] : $existing['image'];

        // Pastikan category & type ada di lookup table
        $catId  = ensureLookup($this->db, 'category', $category);
        $typeId = ensureLookup($this->db, 'type', $type);

        // Prepare statement
        $sql = "UPDATE produk 
            SET name=?, brand=?, category_id=?, type_id=?, price=?, image=?
            WHERE id=?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        // Bind param (semua harus variabel)
        $stmt->bind_param(
            "ssiiisi",
            $name,
            $brand,
            $catId,
            $typeId,
            $price,
            $image,
            $id
        );

        // Eksekusi
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }


    /** Hapus produk */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM produk WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}
