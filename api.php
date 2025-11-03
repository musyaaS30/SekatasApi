<!-- // INI GAUSAH DI APAPAIN // -->
<!-- // INI GAUSAH DI APAPAIN // -->
<!-- // INI GAUSAH DI APAPAIN // -->
<!-- // INI GAUSAH DI APAPAIN // -->
<!-- // INI GAUSAH DI APAPAIN // -->
<!-- // INI GAUSAH DI APAPAIN // -->
<!-- // INI GAUSAH DI APAPAIN // -->
<!-- // INI GAUSAH DI APAPAIN // -->
<!-- // INI GAUSAH DI APAPAIN // -->
<!-- // INI GAUSAH DI APAPAIN // -->
<!-- // INI GAUSAH DI APAPAIN // -->







<?php
// api.php
header("Content-Type: application/json; charset=utf-8");
// Allow CORS (ubah domain jika perlu)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Simple preflight handling
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/*
 DB CONFIG - sesuaikan dengan environment kamu
*/
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = ''; // isi password jika ada
$dbName = 'db_sekatas';

/* koneksi */
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed', 'detail' => $mysqli->connect_error]);
    exit;
}
$mysqli->set_charset("utf8mb4");

/* --- ROUTING sederhana --- 
   URL example: /api.php/products or /api.php/products/2
*/
// Routing sederhana (lebih aman di semua server)
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$script = basename(__FILE__); // nama file: api.php
$parts = explode('/', $uri);

// cari posisi "api.php" dalam path
$pos = array_search($script, $parts);
$segments = [];
if ($pos !== false) {
    // ambil sisa setelah api.php
    $segments = array_slice($parts, $pos + 1);
}

// Tetapkan resource dan id
$resource = isset($segments[0]) ? $segments[0] : null;
$id = isset($segments[1]) ? intval($segments[1]) : null;


// Alternatively support query param style: ?resource=products&id=2
if (empty($segments) && isset($_GET['resource'])) {
    $resource = $_GET['resource'];
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
} else {
    $resource = isset($segments[0]) && $segments[0] !== '' ? $segments[0] : null;
    $id = isset($segments[1]) ? intval($segments[1]) : null;
}

$method = $_SERVER['REQUEST_METHOD'];

/* Helper: read JSON body */
function getJsonBody() {
    $body = file_get_contents('php://input');
    $data = json_decode($body, true);
    return is_array($data) ? $data : null;
}

/* Helper: send JSON and exit */
function respond($data, $status = 200) {
    http_response_code($status);
    if ($data !== null) echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/* Validate resource */
if ($resource !== 'products') {
    respond(['error' => 'Resource not found'], 404);
}

/* --- Implementasi CRUD untuk produk --- */
/* GET /products  and GET /products/{id} */
if ($method === 'GET') {
    if ($id) {
        // ambil detail produk with JOIN ke category & type
        $sql = "SELECT p.id, p.name, p.brand, c.name AS category, t.name AS `type`, p.price, p.image
                FROM produk p
                LEFT JOIN category c ON p.category_id = c.id
                LEFT JOIN type t ON p.type_id = t.id
                WHERE p.id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        if (!$row) respond(['error' => 'Product not found'], 404);
        respond($row, 200);
    } else {
        // list all products
        $sql = "SELECT p.id, p.name, p.brand, c.name AS category, t.name AS `type`, p.price, p.image
                FROM produk p
                LEFT JOIN category c ON p.category_id = c.id
                LEFT JOIN type t ON p.type_id = t.id
                ORDER BY p.id ASC";
        $res = $mysqli->query($sql);
        $rows = [];
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        respond($rows, 200);
    }
}

/* POST /products => create new product
   expected JSON body:
   {
     "name": "string",
     "brand": "string",
     "category": "Biasa",
     "type": "Pria",
     "price": 123000,
     "image": "https://..."
   }
*/
if ($method === 'POST') {
    $data = getJsonBody();
    if (!$data) respond(['error' => 'Invalid JSON body'], 400);

    // basic validation
    foreach (['name','brand','category','type','price','image'] as $f) {
        if (!isset($data[$f]) || $data[$f] === '') {
            respond(['error' => "Field '$f' is required"], 400);
        }
    }

    // Ensure category exists (or create)
    $catId = ensureLookup($mysqli, 'category', $data['category']);
    $typeId = ensureLookup($mysqli, 'type', $data['type']);

    $sql = "INSERT INTO produk (name, brand, category_id, type_id, price, image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ssiiis", $data['name'], $data['brand'], $catId, $typeId, $data['price'], $data['image']);
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        // return created object
        $stmt->close();
        $sql2 = "SELECT p.id, p.name, p.brand, c.name AS category, t.name AS `type`, p.price, p.image
                 FROM produk p
                 LEFT JOIN category c ON p.category_id = c.id
                 LEFT JOIN type t ON p.type_id = t.id
                 WHERE p.id = ?";
        $s2 = $mysqli->prepare($sql2);
        $s2->bind_param("i", $newId);
        $s2->execute();
        $res = $s2->get_result()->fetch_assoc();
        respond($res, 201);
    } else {
        respond(['error' => 'Insert failed', 'detail' => $stmt->error], 500);
    }
}

/* PUT /products/{id} => update product */
if ($method === 'PUT') {
    if (!$id) respond(['error' => 'Product ID required in URL'], 400);
    $data = getJsonBody();
    if (!$data) respond(['error' => 'Invalid JSON body'], 400);

    // get existing
    $check = $mysqli->prepare("SELECT id FROM produk WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $r = $check->get_result()->fetch_assoc();
    if (!$r) respond(['error' => 'Product not found'], 404);

    // allow partial updates: use existing values if not provided
    $sqlGet = "SELECT p.id, p.name, p.brand, c.name AS category, t.name AS `type`, p.price, p.image
               FROM produk p
               LEFT JOIN category c ON p.category_id = c.id
               LEFT JOIN type t ON p.type_id = t.id
               WHERE p.id = ?";
    $sG = $mysqli->prepare($sqlGet);
    $sG->bind_param("i", $id);
    $sG->execute();
    $existing = $sG->get_result()->fetch_assoc();

    $name = isset($data['name']) ? $data['name'] : $existing['name'];
    $brand = isset($data['brand']) ? $data['brand'] : $existing['brand'];
    $category = isset($data['category']) ? $data['category'] : $existing['category'];
    $type = isset($data['type']) ? $data['type'] : $existing['type'];
    $price = isset($data['price']) ? $data['price'] : $existing['price'];
    $image = isset($data['image']) ? $data['image'] : $existing['image'];

    $catId = ensureLookup($mysqli, 'category', $category);
    $typeId = ensureLookup($mysqli, 'type', $type);

    $sql = "UPDATE produk SET name = ?, brand = ?, category_id = ?, type_id = ?, price = ?, image = ? WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ssiiisi", $name, $brand, $catId, $typeId, $price, $image, $id);
    if ($stmt->execute()) {
        respond(['message' => 'Product updated']);
    } else {
        respond(['error' => 'Update failed', 'detail' => $stmt->error], 500);
    }
}

/* DELETE /products/{id} */
if ($method === 'DELETE') {
    if (!$id) respond(['error' => 'Product ID required in URL'], 400);
    $sql = "DELETE FROM produk WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows === 0) respond(['error' => 'Product not found'], 404);
        respond(['message' => 'Product deleted']);
    } else {
        respond(['error' => 'Delete failed', 'detail' => $stmt->error], 500);
    }
}

/* Default: method not allowed */
respond(['error' => 'Method not allowed'], 405);

/* --------------------------------------------------------------------------------
   Helper function: ensureLookup
   - checks if a value exists in lookup table (category/type)
   - if not exist, inserts it and returns id
----------------------------------------------------------------------------------*/
function ensureLookup($mysqli, $table, $name) {
    // allowed tables only
    $allowed = ['category', 'type'];
    if (!in_array($table, $allowed)) throw new Exception("Invalid lookup table");

    // try select
    $sql = "SELECT id FROM {$table} WHERE name = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return intval($row['id']);
    }
    // insert new
    $ins = $mysqli->prepare("INSERT INTO {$table} (name) VALUES (?)");
    $ins->bind_param("s", $name);
    $ins->execute();
    return $ins->insert_id;
}
