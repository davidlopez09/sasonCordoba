<?php

require __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config.php';
$corsOrigin = $config['cors']['origin'] ?? '*';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: ' . $corsOrigin);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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
    
    // Manejar Autenticación
    if (strpos($path, '/api/auth/') !== false) {
        $pathParts = explode('/', trim(strstr($path, '/api/auth/'), '/'));
        $authController = new \App\Presentation\AuthController(
            new \App\Application\Auth\LoginUseCase($config['jwt']['secret'], $config['jwt']['expiration'])
        );
        $authController->handleRequest($method, $pathParts);
    }

    // Rutas Administrativas
    if (strpos($path, '/api/admin/') !== false) {
        $isAuthenticated = false;

        // 1. Intentar validar por sesión (legacy support)
        session_start(['cookie_samesite' => 'Lax']);
        if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            $isAuthenticated = true;
        }

        // 2. Intentar validar por JWT (nueva arquitectura)
        if (!$isAuthenticated) {
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                try {
                    $token = $matches[1];
                    \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($config['jwt']['secret'], 'HS256'));
                    $isAuthenticated = true;
                } catch (Exception $e) {
                    // Token inválido
                }
            }
        }

        if (!$isAuthenticated) {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }

        $pathParts = explode('/', trim(strstr($path, '/api/admin/'), '/'));
        $resource = $pathParts[2] ?? ''; // [0]=>api, [1]=>admin, [2]=>exponentes

        $storageService = new \App\Infrastructure\Services\SupabaseStorageService($config);

        if ($resource === 'settings') {
            $settingRepo = new \App\Infrastructure\Repositories\EloquentSettingRepository();
            $adminController = new \App\Presentation\AdminSettingsController(
                new \App\Application\Admin\Setting\UpdateSettingUseCase($settingRepo, $storageService)
            );
            $adminController->handleRequest($method);
        } elseif ($resource === 'export') {
            $visitorRepo = new \App\Infrastructure\Repositories\EloquentVisitorRepository();
            $exhibitorRepo = new \App\Infrastructure\Repositories\EloquentExhibitorRepository();
            
            $adminController = new \App\Presentation\AdminExportController(
                new \App\Application\Admin\Export\ExportVisitorsUseCase($visitorRepo),
                new \App\Application\Admin\Export\ExportExhibitorsUseCase($exhibitorRepo)
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'exponentes') {
            $repo = new \App\Infrastructure\Repositories\EloquentExponenteRepository();
            $adminController = new \App\Presentation\AdminExponentesController(
                new \App\Application\Admin\Exponente\CreateExponenteUseCase($repo, $storageService),
                new \App\Application\Admin\Exponente\UpdateExponenteUseCase($repo, $storageService),
                new \App\Application\Admin\Exponente\DeleteExponenteUseCase($repo, $storageService),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'platillos') {
            $repo = new \App\Infrastructure\Repositories\EloquentPlatilloRepository();
            $adminController = new \App\Presentation\AdminPlatillosController(
                new \App\Application\Admin\Platillo\CreatePlatilloUseCase($repo, $storageService),
                new \App\Application\Admin\Platillo\UpdatePlatilloUseCase($repo, $storageService),
                new \App\Application\Admin\Platillo\DeletePlatilloUseCase($repo, $storageService),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'itinerario') {
            $repo = new \App\Infrastructure\Repositories\EloquentItinerarioRepository();
            $adminController = new \App\Presentation\AdminItinerariosController(
                new \App\Application\Admin\Itinerario\CreateItinerarioUseCase($repo),
                new \App\Application\Admin\Itinerario\UpdateItinerarioUseCase($repo),
                new \App\Application\Admin\Itinerario\DeleteItinerarioUseCase($repo),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'patrocinadores') {
            $repo = new \App\Infrastructure\Repositories\EloquentPatrocinadorRepository();
            $adminController = new \App\Presentation\AdminPatrocinadorsController(
                new \App\Application\Admin\Patrocinador\CreatePatrocinadorUseCase($repo, $storageService),
                new \App\Application\Admin\Patrocinador\UpdatePatrocinadorUseCase($repo, $storageService),
                new \App\Application\Admin\Patrocinador\DeletePatrocinadorUseCase($repo, $storageService),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'slides') {
            $repo = new \App\Infrastructure\Repositories\EloquentSlideRepository();
            $adminController = new \App\Presentation\AdminSlidesController(
                new \App\Application\Admin\Slide\CreateSlideUseCase($repo, $storageService),
                new \App\Application\Admin\Slide\UpdateSlideUseCase($repo, $storageService),
                new \App\Application\Admin\Slide\DeleteSlideUseCase($repo, $storageService),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'botones_hero') {
            $repo = new \App\Infrastructure\Repositories\EloquentBotonHeroRepository();
            $adminController = new \App\Presentation\AdminBotonHerosController(
                new \App\Application\Admin\BotonHero\CreateBotonHeroUseCase($repo),
                new \App\Application\Admin\BotonHero\UpdateBotonHeroUseCase($repo),
                new \App\Application\Admin\BotonHero\DeleteBotonHeroUseCase($repo),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'badges') {
            $repo = new \App\Infrastructure\Repositories\EloquentBadgeRepository();
            $adminController = new \App\Presentation\AdminBadgesController(
                new \App\Application\Admin\Badge\CreateBadgeUseCase($repo),
                new \App\Application\Admin\Badge\UpdateBadgeUseCase($repo),
                new \App\Application\Admin\Badge\DeleteBadgeUseCase($repo),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'menu_nav') {
            $repo = new \App\Infrastructure\Repositories\EloquentMenuNavRepository();
            $adminController = new \App\Presentation\AdminMenuNavsController(
                new \App\Application\Admin\MenuNav\CreateMenuNavUseCase($repo),
                new \App\Application\Admin\MenuNav\UpdateMenuNavUseCase($repo),
                new \App\Application\Admin\MenuNav\DeleteMenuNavUseCase($repo),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'botones_nav') {
            $repo = new \App\Infrastructure\Repositories\EloquentBotonNavRepository();
            $adminController = new \App\Presentation\AdminBotonNavsController(
                new \App\Application\Admin\BotonNav\CreateBotonNavUseCase($repo),
                new \App\Application\Admin\BotonNav\UpdateBotonNavUseCase($repo),
                new \App\Application\Admin\BotonNav\DeleteBotonNavUseCase($repo),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'faq') {
            $repo = new \App\Infrastructure\Repositories\EloquentFaqRepository();
            $adminController = new \App\Presentation\AdminFaqsController(
                new \App\Application\Admin\Faq\CreateFaqUseCase($repo),
                new \App\Application\Admin\Faq\UpdateFaqUseCase($repo),
                new \App\Application\Admin\Faq\DeleteFaqUseCase($repo),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'caracteristicas') {
            $repo = new \App\Infrastructure\Repositories\EloquentCaracteristicaRepository();
            $adminController = new \App\Presentation\AdminCaracteristicasController(
                new \App\Application\Admin\Caracteristica\CreateCaracteristicaUseCase($repo),
                new \App\Application\Admin\Caracteristica\UpdateCaracteristicaUseCase($repo),
                new \App\Application\Admin\Caracteristica\DeleteCaracteristicaUseCase($repo),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'estadisticas') {
            $repo = new \App\Infrastructure\Repositories\EloquentEstadisticaRepository();
            $adminController = new \App\Presentation\AdminEstadisticasController(
                new \App\Application\Admin\Estadistica\CreateEstadisticaUseCase($repo),
                new \App\Application\Admin\Estadistica\UpdateEstadisticaUseCase($repo),
                new \App\Application\Admin\Estadistica\DeleteEstadisticaUseCase($repo),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'footer') {
            $repo = new \App\Infrastructure\Repositories\EloquentFooterRepository();
            $adminController = new \App\Presentation\AdminFootersController(
                new \App\Application\Admin\Footer\CreateFooterUseCase($repo),
                new \App\Application\Admin\Footer\UpdateFooterUseCase($repo),
                new \App\Application\Admin\Footer\DeleteFooterUseCase($repo),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'secciones_dinamicas') {
            $repo = new \App\Infrastructure\Repositories\EloquentSeccionDinamicaRepository();
            $adminController = new \App\Presentation\AdminSeccionDinamicasController(
                new \App\Application\Admin\SeccionDinamica\CreateSeccionDinamicaUseCase($repo),
                new \App\Application\Admin\SeccionDinamica\UpdateSeccionDinamicaUseCase($repo),
                new \App\Application\Admin\SeccionDinamica\DeleteSeccionDinamicaUseCase($repo),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'bloques_dinamicos') {
            $repo = new \App\Infrastructure\Repositories\EloquentBloqueDinamicoRepository();
            $adminController = new \App\Presentation\AdminBloqueDinamicosController(
                new \App\Application\Admin\BloqueDinamico\CreateBloqueDinamicoUseCase($repo),
                new \App\Application\Admin\BloqueDinamico\UpdateBloqueDinamicoUseCase($repo),
                new \App\Application\Admin\BloqueDinamico\DeleteBloqueDinamicoUseCase($repo),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'botones_participa') {
            $repo = new \App\Infrastructure\Repositories\EloquentBotonParticipaRepository();
            $adminController = new \App\Presentation\AdminBotonParticipasController(
                new \App\Application\Admin\BotonParticipa\CreateBotonParticipaUseCase($repo),
                new \App\Application\Admin\BotonParticipa\UpdateBotonParticipaUseCase($repo),
                new \App\Application\Admin\BotonParticipa\DeleteBotonParticipaUseCase($repo),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'directorio') {
            $repo = new \App\Infrastructure\Repositories\EloquentDirectorioRepository();
            $adminController = new \App\Presentation\AdminDirectoriosController(
                new \App\Application\Admin\Directorio\CreateDirectorioUseCase($repo, $storageService),
                new \App\Application\Admin\Directorio\UpdateDirectorioUseCase($repo, $storageService),
                new \App\Application\Admin\Directorio\DeleteDirectorioUseCase($repo, $storageService),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } elseif ($resource === 'galeria') {
            $repo = new \App\Infrastructure\Repositories\EloquentGaleriaRepository();
            $adminController = new \App\Presentation\AdminGaleriasController(
                new \App\Application\Admin\Galeria\CreateGaleriaUseCase($repo),
                new \App\Application\Admin\Galeria\UpdateGaleriaUseCase($repo),
                new \App\Application\Admin\Galeria\DeleteGaleriaUseCase($repo),
                $repo
            );
            $adminController->handleRequest($method, $pathParts);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint de admin no encontrado']);
            exit;
        }
        exit;
    }

    // Manejar Petición Pública
    $apiController->handleRequest($method, $path, $route);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
    exit;
}

