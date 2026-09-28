<?php
// Conexión a MySQL.
// En producción manda la variable de entorno; en local se usa el respaldo de abajo.

function entorno(string $clave, string $porDefecto): string
{
    $valor = getenv($clave);
    return $valor === false ? $porDefecto : $valor;
}

define('DB_HOST',     entorno('DB_HOST', 'localhost'));
define('DB_PORT',     entorno('DB_PORT', ''));
define('DB_NAME',     entorno('DB_NAME', 'inventario_remates'));
define('DB_USERNAME', entorno('DB_USERNAME', 'root'));
define('DB_PASSWORD', entorno('DB_PASSWORD', ''));
define('DB_CHARSET',  'utf8mb4');
