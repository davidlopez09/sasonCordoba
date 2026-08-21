<?php

require __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config.php';
$corsOrigin = $config['cors']['origin'] ?? '*';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . $corsOrigin);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

try {
    // Inicializar DB (Eloquent)
    \App\Infrastructure\Database::init($config);

    // Inicializar dependencias
    $mailService = new \App\Infrastructure\PhpMailService();

    // Repositorios
    $visitorRepo = new \App\Infrastructure\Repositories\EloquentVisitorRepository();
    $exhibitorRepo = new \App\Infrastructure\Repositories\EloquentExhibitorRepository();
    $contactRepo = new \App\Infrastructure\Repositories\EloquentContactRepository();
    $siteDataRepo = new \App\Infrastructure\Repositories\EloquentSiteDataRepository();

    // Casos de Uso
    $registerVisitorUseCase = new \App\Application\RegisterVisitorUseCase($visitorRepo, $mailService);
    $registerExhibitorUseCase = new \App\Application\RegisterExhibitorUseCase($exhibitorRepo, $mailService);
    $submitContactUseCase = new \App\Application\SubmitContactUseCase($contactRepo);
    $getSiteDataUseCase = new \App\Application\GetSiteDataUseCase($siteDataRepo);

    // Controlador
    $apiController = new \App\Presentation\ApiController(
        $registerVisitorUseCase,
        $registerExhibitorUseCase,
        $submitContactUseCase,
        $getSiteDataUseCase
    );

    // Parsear ruta
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = rtrim($path, '/');
    $method = $_SERVER['REQUEST_METHOD'];
    $route = $_GET['route'] ?? '';

    // Manejar Petición
    $apiController->handleRequest($method, $path, $route);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
    exit;
}

