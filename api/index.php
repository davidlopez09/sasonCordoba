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
require __DIR__ . '/mail.php';

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
$isNavRoute = $method === 'GET' && $route === 'nav';
$isTerminosRoute = $method === 'GET' && $route === 'terminos';
$isRegistroVisitanteRoute = $method === 'POST' && $route === 'registro_visitante';
$isRegistroExpositorRoute = $method === 'POST' && $route === 'registro_expositor';
$isContactoRoute = $method === 'POST' && $route === 'contacto';

function validarCorreo(?string $correo): string
{
    $correo = trim((string) $correo);
    if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        jsonError('Correo electrónico no válido', 422);
    }
    return $correo;
}

function requerido(?string $valor, string $campo): string
{
    $valor = trim((string) $valor);
    if ($valor === '') {
        jsonError("El campo \"$campo\" es obligatorio", 422);
    }
    return $valor;
}

if ($isRegistroVisitanteRoute) {
    $nombre = requerido($_POST['nombre'] ?? null, 'Nombre');
    $correo = validarCorreo($_POST['correo'] ?? null);
    $telefono = trim($_POST['telefono'] ?? '') ?: null;

    $stmt = $db->prepare('INSERT INTO registros_visitantes (nombre, correo, telefono) VALUES (?, ?, ?)');
    $stmt->execute([$nombre, $correo, $telefono]);

    sendConfirmationEmail(
        $correo,
        'Confirmación de registro - Sazón Córdoba',
        "Hola $nombre,\n\nTu registro como visitante a Sazón Córdoba fue recibido exitosamente.\n\nTe esperamos el 11 y 12 de septiembre en el Centro de Eventos de Montería.\n\n¡Nos vemos allá!"
    );

    jsonResponse(['ok' => true]);
}

if ($isRegistroExpositorRoute) {
    $nombreEmpresa = requerido($_POST['nombre_empresa'] ?? null, 'Nombre del negocio');
    $nombreContacto = requerido($_POST['nombre_contacto'] ?? null, 'Nombre de contacto');
    $correo = validarCorreo($_POST['correo'] ?? null);
    $categoria = trim($_POST['categoria'] ?? '') ?: 'Gastronomía';
    $telefono = trim($_POST['telefono'] ?? '') ?: null;
    $descripcion = trim($_POST['descripcion'] ?? '') ?: null;

    $stmt = $db->prepare('INSERT INTO registros_expositores (nombre_empresa, categoria, nombre_contacto, correo, telefono, descripcion) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$nombreEmpresa, $categoria, $nombreContacto, $correo, $telefono, $descripcion]);

    sendConfirmationEmail(
        $correo,
        'Postulación recibida - Sazón Córdoba',
        "Hola $nombreContacto,\n\nRecibimos la postulación de \"$nombreEmpresa\" como expositor de Sazón Córdoba.\n\nLa organización revisará tu información y se pondrá en contacto contigo pronto.\n\n¡Gracias por querer hacer parte del evento!"
    );

    jsonResponse(['ok' => true]);
}

if ($isContactoRoute) {
    $nombre = requerido($_POST['nombre'] ?? null, 'Nombre');
    $correo = validarCorreo($_POST['correo'] ?? null);
    $telefono = trim($_POST['telefono'] ?? '') ?: null;
    $mensaje = requerido($_POST['mensaje'] ?? null, 'Mensaje');

    $stmt = $db->prepare('INSERT INTO mensajes_contacto (nombre, correo, telefono, mensaje) VALUES (?, ?, ?, ?)');
    $stmt->execute([$nombre, $correo, $telefono, $mensaje]);

    jsonResponse(['ok' => true]);
}

if ($isTerminosRoute) {
    $terminos = $db->query('SELECT contenido FROM terminos_condiciones LIMIT 1')->fetchColumn();
    jsonResponse(['contenido' => $terminos ?: '']);
}

if ($isNavRoute) {
    $data = [];

    $data['menu_nav'] = $db->query('SELECT * FROM menu_navegacion WHERE activo = true ORDER BY orden')->fetchAll();
    $data['botones_nav'] = $db->query('SELECT * FROM botones_nav WHERE activo = true ORDER BY orden')->fetchAll();
    $data['configuraciones'] = [];

    $logoActivo = $db->query('SELECT logo FROM logos_nav WHERE activo = true LIMIT 1')->fetchColumn();
    if ($logoActivo) {
        $data['configuraciones']['logo_nav'] = $logoActivo;
    }

    $colorNavFondo = $db->query("SELECT valor FROM configuraciones_sitio WHERE clave = 'color_nav_fondo'")->fetchColumn();
    if ($colorNavFondo) {
        $data['configuraciones']['color_nav_fondo'] = $colorNavFondo;
    }

    jsonResponse($data);
}

if ($isSiteRoute) {
    $data = [];

    // Hero slides
    $stmt = $db->query('SELECT * FROM banner_principal WHERE activo = true ORDER BY orden');
    $data['hero']['slides'] = $stmt->fetchAll();

    // Hero texto (único, no varía por slide)
    $data['hero']['texto'] = $db->query('SELECT * FROM hero_texto LIMIT 1')->fetch() ?: null;

    // Hero botones
    $data['hero']['botones'] = $db->query('SELECT * FROM botones_hero WHERE activo = true ORDER BY orden')->fetchAll();

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

    // Botones del nav
    $stmt = $db->query('SELECT * FROM botones_nav WHERE activo = true ORDER BY orden');
    $data['botones_nav'] = $stmt->fetchAll();

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
            'color' => $row['color'],
        ];
    }

    // Configuraciones (clave => valor)
    $stmt = $db->query('SELECT clave, valor FROM configuraciones_sitio');
    $data['configuraciones'] = [];
    while ($row = $stmt->fetch()) {
        $data['configuraciones'][$row['clave']] = $row['valor'];
    }

    // Logo activo del nav
    $logoActivo = $db->query('SELECT logo FROM logos_nav WHERE activo = true LIMIT 1')->fetchColumn();
    if ($logoActivo) {
        $data['configuraciones']['logo_nav'] = $logoActivo;
    }

    // Secciones dinámicas (constructor de secciones por bloques)
    $seccionesDin = $db->query('SELECT * FROM secciones_dinamicas WHERE activo = true ORDER BY orden')->fetchAll();
    foreach ($seccionesDin as &$sd) {
        $stmt = $db->prepare('SELECT * FROM bloques_dinamicos WHERE seccion_id = ? ORDER BY orden');
        $stmt->execute([$sd['id']]);
        $bloques = $stmt->fetchAll();
        foreach ($bloques as &$b) {
            $b['contenido'] = json_decode($b['contenido'], true);
        }
        unset($b);
        $sd['bloques'] = $bloques;
    }
    unset($sd);
    $data['secciones_dinamicas'] = $seccionesDin;

    // Haz Parte
    $data['participa']['seccion'] = $db->query('SELECT * FROM seccion_participa WHERE activo = true LIMIT 1')->fetch() ?: null;
    $data['participa']['botones'] = $db->query('SELECT * FROM botones_participa WHERE activo = true ORDER BY orden')->fetchAll();

    // Directorio de Expositores
    $data['directorio_expositores'] = $db->query('SELECT * FROM directorio_expositores WHERE activo = true ORDER BY orden')->fetchAll();

    // Galería
    $data['galeria'] = $db->query('SELECT * FROM galeria_items WHERE activo = true ORDER BY orden')->fetchAll();

    jsonResponse($data);
}

jsonError('Ruta no encontrada', 404);
