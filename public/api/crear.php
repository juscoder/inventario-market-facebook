<?php
// API: Crear producto (POST) - Sube foto a Cloudinary y guarda en MySQL

use App\Controllers\ProductoController;

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../app/init.php';
require_once __DIR__ . '/../../core/cloudinary.php';

requiereAutenticacionJson();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$respuesta = (new ProductoController())->crear($_POST, $_FILES);
http_response_code($respuesta['status']);
echo json_encode($respuesta['body']);
