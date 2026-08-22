<?php

namespace App\Presentation;

use App\Application\Admin\SeccionDinamica\CreateSeccionDinamicaUseCase;
use App\Application\Admin\SeccionDinamica\UpdateSeccionDinamicaUseCase;
use App\Application\Admin\SeccionDinamica\DeleteSeccionDinamicaUseCase;
use App\Domain\Interfaces\SeccionDinamicaRepositoryInterface;
use Exception;

class AdminSeccionDinamicasController
{
    private $createUseCase;
    private $updateUseCase;
    private $deleteUseCase;
    private $repository;

    public function __construct(
        CreateSeccionDinamicaUseCase $createUseCase,
        UpdateSeccionDinamicaUseCase $updateUseCase,
        DeleteSeccionDinamicaUseCase $deleteUseCase,
        SeccionDinamicaRepositoryInterface $repository
    ) {
        $this->createUseCase = $createUseCase;
        $this->updateUseCase = $updateUseCase;
        $this->deleteUseCase = $deleteUseCase;
        $this->repository = $repository;
    }

    public function handleRequest(string $method, array $pathParts)
    {
        try {
            $id = isset($pathParts[3]) ? (int)$pathParts[3] : null;
            $actualMethod = $method;
            if ($method === 'POST' && isset($_POST['_method'])) $actualMethod = strtoupper($_POST['_method']);

            if ($actualMethod === 'GET' && !$id) return $this->jsonResponse($this->repository->getAll());
            if ($actualMethod === 'POST' && !$id) {
                $this->createUseCase->execute($_POST);
                return $this->jsonResponse(['ok' => true]);
            }
            if ($actualMethod === 'PUT' && $id) {
                $this->updateUseCase->execute($id, $_POST);
                return $this->jsonResponse(['ok' => true]);
            }
            if ($actualMethod === 'DELETE' && $id) {
                $this->deleteUseCase->execute($id);
                return $this->jsonResponse(['ok' => true]);
            }
            return $this->jsonError('Ruta no encontrada', 404);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
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
