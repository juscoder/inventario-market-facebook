<?php
// Subida de imágenes a Cloudinary (Upload Preset Unsigned) mediante cURL

require_once __DIR__ . '/config.php';

/**
 * Envía una imagen temporal ($_FILES) a Cloudinary y retorna su URL segura.
 *
 * @param array $archivo Elemento de $_FILES (con 'tmp_name' y 'error').
 * @return array{success: bool, url?: string, message?: string}
 */
function subirImagenCloudinary(array $archivo): array
{
    // Validar que no hubo errores en la subida del formulario
    if (!isset($archivo['tmp_name']) || $archivo['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No se recibió una imagen válida.'];
    }

    // Validar que sea una imagen real
    $mime = mime_content_type($archivo['tmp_name']);
    $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $permitidos, true)) {
        return ['success' => false, 'message' => 'Formato no permitido. Solo JPG, PNG o WEBP.'];
    }

    $endpoint = 'https://api.cloudinary.com/v1_1/' . CLOUDINARY_CLOUD_NAME . '/image/upload';

    $post = [
        'file'          => new CURLFile($archivo['tmp_name'], $mime, $archivo['name'] ?? 'imagen.jpg'),
        'upload_preset' => CLOUDINARY_UPLOAD_PRESET,
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
    ]);

    $respuesta = curl_exec($ch);
    $errorCurl = curl_error($ch);
    curl_close($ch);

    if ($respuesta === false) {
        return ['success' => false, 'message' => 'Error de conexión con Cloudinary: ' . $errorCurl];
    }

    $datos = json_decode($respuesta, true);

    if (!empty($datos['secure_url'])) {
        return ['success' => true, 'url' => $datos['secure_url']];
    }

    $mensaje = $datos['error']['message'] ?? 'Cloudinary rechazó la imagen.';
    return ['success' => false, 'message' => $mensaje];
}
