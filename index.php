<?php
require_once __DIR__ . '/helpers/cors.php';
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/config/database.php';

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = explode('/', $uri);

// cari posisi 'index.php' atau 'api.php'
$script = basename(__FILE__);
$pos = array_search($script, $parts);
$segments = $pos !== false ? array_slice($parts, $pos + 1) : $parts;

$resource = $segments[0] ?? null;
$id = isset($segments[1]) ? intval($segments[1]) : null;

switch ($resource) {
    case 'products':
        require_once __DIR__ . '/routes/products.php';
        break;

    default:
        respond(['error' => 'Resource not found'], 404);
}
