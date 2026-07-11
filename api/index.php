<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require __DIR__ . '/db.php';

function jsonResponse($data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function jsonError(string $message, int $code = 400): void
{
    jsonResponse(['error' => $message], $code);
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route'] ?? '';

try {
    $db = getDB();
} catch (PDOException $e) {
    jsonError('Error de conexión a la base de datos', 500);
}

$isSiteRoute = $method === 'GET' && (
    $path === '/api/site' ||
    str_ends_with($path, '/api/site') ||
    $route === 'site' ||
    basename($path) === 'index.php'
);

if ($isSiteRoute) {
    $data = [];

    // Hero slides
    $stmt = $db->query('SELECT * FROM banner_principal WHERE activo = true ORDER BY orden');
    $data['hero']['slides'] = $stmt->fetchAll();

    // Hero estadisticas
    $stmt = $db->query('SELECT * FROM estadisticas_principales ORDER BY orden');
    $data['hero']['estadisticas'] = $stmt->fetchAll();

    // About seccion
    $stmt = $db->query('SELECT * FROM secciones_about LIMIT 1');
    $data['about']['seccion'] = $stmt->fetch() ?: null;

    // About caracteristicas
    $stmt = $db->query('SELECT * FROM caracteristicas_about ORDER BY orden');
    $data['about']['caracteristicas'] = $stmt->fetchAll();

    // Exponentes
    $stmt = $db->query('SELECT * FROM exponentes ORDER BY orden');
    $data['exponentes'] = $stmt->fetchAll();

    // Platillos destacados
    $stmt = $db->query('SELECT * FROM platillos_destacados ORDER BY orden');
    $data['platillos_destacados'] = $stmt->fetchAll();

    // Itinerario
    $stmt = $db->query('SELECT * FROM itinerario_items ORDER BY orden');
    $data['itinerario'] = $stmt->fetchAll();

    // Patrocinadores
    $stmt = $db->query('SELECT * FROM patrocinadores ORDER BY orden');
    $data['patrocinadores'] = $stmt->fetchAll();

    // Sección Identidad
    $stmt = $db->query('SELECT * FROM seccion_identidad WHERE activo = true LIMIT 1');
    $data['identidad']['seccion'] = $stmt->fetch() ?: null;

    // Badges Identidad
    $stmt = $db->query('SELECT * FROM badges_identidad ORDER BY orden');
    $data['identidad']['badges'] = $stmt->fetchAll();

    // Menú de navegación
    $stmt = $db->query('SELECT * FROM menu_navegacion WHERE activo = true ORDER BY orden');
    $data['menu_nav'] = $stmt->fetchAll();

    // FAQ items
    $stmt = $db->query('SELECT * FROM preguntas_frecuentes WHERE activo = true ORDER BY orden');
    $data['faq'] = $stmt->fetchAll();

    // Footer
    $stmt = $db->query('SELECT * FROM pie_pagina ORDER BY columna, orden');
    $data['footer'] = $stmt->fetchAll();

    // Subtítulos de secciones
    $stmt = $db->query('SELECT * FROM secciones_subtitulos');
    $data['subtitulos'] = [];
    while ($row = $stmt->fetch()) {
        $data['subtitulos'][$row['seccion']] = [
            'titulo' => $row['titulo'],
            'subtitulo' => $row['subtitulo'],
        ];
    }

    // Configuraciones (clave => valor)
    $stmt = $db->query('SELECT clave, valor FROM configuraciones_sitio');
    $data['configuraciones'] = [];
    while ($row = $stmt->fetch()) {
        $data['configuraciones'][$row['clave']] = $row['valor'];
    }

    jsonResponse($data);
}

jsonError('Ruta no encontrada', 404);
