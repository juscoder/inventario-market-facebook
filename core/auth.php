<?php
// Validación de sesión PHP

require_once __DIR__ . '/database.php';

const IDLE_TIMEOUT_SEGUNDOS = 1800; // 30 minutos de inactividad

/**
 * Prefijo de la subcarpeta desde la que se sirve el proyecto.
 * '' en la raíz del dominio, '/inventario-market-facebook' en una subcarpeta.
 * La raíz web es un nivel por encima de la única subcarpeta pública: /admin
 */
function rutaBase(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }

    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    if (basename($dir) === 'admin') {
        $dir = dirname($dir);
    }

    $base = ($dir === '/' || $dir === '' || $dir === '.') ? '' : rtrim($dir, '/');

    return $base;
}

/**
 * Inicia la sesión con cookies endurecidas (httponly, samesite, secure si HTTPS)
 */
function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_name('inventario_remates');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Destruye la sesión actual y borra su cookie
 */
function destruirSesion(): void
{
    iniciarSesion();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

/**
 * True si la sesión no existe o superó el tiempo de inactividad
 */
function sesionExpirada(): bool
{
    if (empty($_SESSION['ultimo_acceso'])) {
        return true;
    }
    return (time() - $_SESSION['ultimo_acceso']) > IDLE_TIMEOUT_SEGUNDOS;
}

/**
 * Protege una vista: si no hay sesión válida, redirige a login.php
 */
function requiereAutenticacion(): void
{
    iniciarSesion();

    if (empty($_SESSION['admin']) || sesionExpirada()) {
        destruirSesion();
        header('Location: ' . rutaBase() . '/login.php');
        exit;
    }

    $_SESSION['ultimo_acceso'] = time();
}

/**
 * Protege un endpoint JSON: si no hay sesión válida, responde 401
 */
function requiereAutenticacionJson(): void
{
    iniciarSesion();

    if (empty($_SESSION['admin']) || sesionExpirada()) {
        destruirSesion();
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'No autorizado.']);
        exit;
    }

    $_SESSION['ultimo_acceso'] = time();
}

/**
 * Valida credenciales contra la tabla usuarios (password_verify)
 */
function credencialesValidas(string $usuario, string $clave): bool
{
    $stmt = getPDO()->prepare('SELECT clave_hash FROM usuarios WHERE usuario = ?');
    $stmt->execute([$usuario]);
    $fila = $stmt->fetch();

    if (!$fila) {
        // Verificación falsa para responder en tiempo similar cuando el usuario no existe
        password_verify($clave, '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG');
        return false;
    }

    return password_verify($clave, $fila['clave_hash']);
}

iniciarSesion();
