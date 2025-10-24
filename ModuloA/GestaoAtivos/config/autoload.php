<?php
// Carrega automaticamente controllers e models (simples)
spl_autoload_register(function($class){
    $paths = [
        __DIR__ . '/../controllers/' . $class . '.php',
        __DIR__ . '/../models/' . $class . '.php',
        __DIR__ . '/../libs/' . $class . '.php'
    ];
    foreach ($paths as $p) {
        if (file_exists($p)) {
            require_once $p;
            return;
        }
    }
});
// Uso: require_once __DIR__ . '/config/autoload.php';
