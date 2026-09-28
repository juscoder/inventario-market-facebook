<?php
// Destrucción de sesión

require_once __DIR__ . '/core/auth.php';

destruirSesion();

header('Location: ' . rutaBase() . '/login.php');
exit;
