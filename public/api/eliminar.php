<?php
// API: Eliminar producto (POST) - Borra el registro en MySQL

use App\Controllers\ProductoController;

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../app/init.php';

requiereAutenticacionJson();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$respuesta = (new ProductoController())->eliminar($_POST);
http_response_code($respuesta['status']);
echo json_encode($respuesta['body']);
