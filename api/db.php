<?php
require_once __DIR__ . '/vendor/autoload.php';
$config = require __DIR__ . '/config.php';

// Inicializar Eloquent para poder reutilizar su conexin PDO subyacente
if (!class_exists('\Illuminate\Database\Capsule\Manager')) {
    throw new Exception("Falta cargar vendor/autoload.php o illuminate/database");
}

try {
    \App\Infrastructure\Database::init($config);
} catch (Exception $e) {
    // Si ya fue inicializado en index.php, ignoramos el error
}

function getDB() {
    return \Illuminate\Database\Capsule\Manager::connection()->getPdo();
}
