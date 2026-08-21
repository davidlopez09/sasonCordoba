<?php

namespace App\Presentation;

use App\Application\RegisterVisitorUseCase;
use App\Application\RegisterExhibitorUseCase;
use App\Application\SubmitContactUseCase;
use App\Application\GetSiteDataUseCase;
use Exception;

class ApiController
{
    private $registerVisitorUseCase;
    private $registerExhibitorUseCase;
    private $submitContactUseCase;
    private $getSiteDataUseCase;

    public function __construct(
        RegisterVisitorUseCase $registerVisitorUseCase,
        RegisterExhibitorUseCase $registerExhibitorUseCase,
        SubmitContactUseCase $submitContactUseCase,
        GetSiteDataUseCase $getSiteDataUseCase
    ) {
        $this->registerVisitorUseCase = $registerVisitorUseCase;
        $this->registerExhibitorUseCase = $registerExhibitorUseCase;
        $this->submitContactUseCase = $submitContactUseCase;
        $this->getSiteDataUseCase = $getSiteDataUseCase;
    }

    public function handleRequest(string $method, string $path, string $route)
    {
        try {
            $isSiteRoute = $method === 'GET' && ($path === '/api/site' || str_ends_with($path, '/api/site') || $route === 'site' || basename($path) === 'index.php');
            $isNavRoute = $method === 'GET' && $route === 'nav';
            $isTerminosRoute = $method === 'GET' && $route === 'terminos';
            $isRegistroVisitanteRoute = $method === 'POST' && $route === 'registro_visitante';
            $isRegistroExpositorRoute = $method === 'POST' && $route === 'registro_expositor';
            $isContactoRoute = $method === 'POST' && $route === 'contacto';

            if ($isRegistroVisitanteRoute) {
                $this->registerVisitorUseCase->execute($_POST);
                return $this->jsonResponse(['ok' => true]);
            }

            if ($isRegistroExpositorRoute) {
                $this->registerExhibitorUseCase->execute($_POST);
                return $this->jsonResponse(['ok' => true]);
            }

            if ($isContactoRoute) {
                $this->submitContactUseCase->execute($_POST);
                return $this->jsonResponse(['ok' => true]);
            }

            if ($isTerminosRoute) {
                $contenido = $this->getSiteDataUseCase->getTerminos();
                return $this->jsonResponse(['contenido' => $contenido]);
            }

            if ($isNavRoute) {
                $data = $this->getSiteDataUseCase->getNavData();
                return $this->jsonResponse($data);
            }

            if ($isSiteRoute) {
                $data = $this->getSiteDataUseCase->execute();
                return $this->jsonResponse($data);
            }

            return $this->jsonError('Ruta no encontrada', 404);

        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            // Prevent returning non-HTTP codes like DB error codes directly if they aren't standard HTTP status codes
            if ($code < 100 || $code > 599) {
                $code = 500;
            }
            return $this->jsonError($e->getMessage(), $code);
        }
    }

    private function jsonResponse(array $data, int $code = 200)
    {
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function jsonError(string $message, int $code = 400)
    {
        $this->jsonResponse(['error' => $message], $code);
    }
}
