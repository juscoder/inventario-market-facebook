<?php
// Conexión PDO a MySQL con manejo de excepciones

require_once __DIR__ . '/config.php';

function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST
            . (DB_PORT !== '' ? ';port=' . DB_PORT : '')
            . ';dbname=' . DB_NAME
            . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Error de conexión a la base de datos.');
        }
    }

    return $pdo;
}
