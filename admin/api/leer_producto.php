<?php
// API: Leer 1 producto (GET) - Para edición

use App\Controllers\ProductoController;

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../app/init.php';

requiereAutenticacionJson();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$respuesta = (new ProductoController())->leer($_GET);
http_response_code($respuesta['status']);
echo json_encode($respuesta['body']);
