<?php
require_once __DIR__ . '/../controllers/ProductController.php';

$controller = new ProductController($mysqli);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $controller->get($id ?? null);
        break;
    case 'POST':
        $controller->create();
        break;
    case 'PUT':
        $controller->update($id ?? null);
        break;
    case 'DELETE':
        $controller->delete($id ?? null);
        break;
    default:
        respond(['error' => 'Method not allowed'], 405);
}
