<?php
// Bootstrap: autoload de clases App\ y núcleo compartido

spl_autoload_register(function (string $clase): void {
    $prefijo = 'App\\';
    if (strncmp($clase, $prefijo, strlen($prefijo)) !== 0) {
        return;
    }

    $ruta = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($clase, strlen($prefijo))) . '.php';
    if (is_file($ruta)) {
        require_once $ruta;
    }
});

require_once dirname(__DIR__) . '/core/auth.php';
