<?php

namespace App\Presentation;

use App\Application\Admin\Exponente\CreateExponenteUseCase;
use App\Application\Admin\Exponente\UpdateExponenteUseCase;
use App\Application\Admin\Exponente\DeleteExponenteUseCase;
use App\Domain\Interfaces\ExponenteRepositoryInterface;
use Exception;

class AdminExponentesController
{
    private $createUseCase;
    private $updateUseCase;
    private $deleteUseCase;
    private $repository;

    public function __construct(
        CreateExponenteUseCase $createUseCase,
        UpdateExponenteUseCase $updateUseCase,
        DeleteExponenteUseCase $deleteUseCase,
        ExponenteRepositoryInterface $repository
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

            // En PHP con FormData a veces se usa POST y un campo '_method' para simular PUT/DELETE
            $actualMethod = $method;
            if ($method === 'POST' && isset($_POST['_method'])) {
                $actualMethod = strtoupper($_POST['_method']);
            }

            if ($actualMethod === 'GET' && !$id) {
                return $this->jsonResponse($this->repository->getAll());
            }

            if ($actualMethod === 'POST' && !$id) {
                $this->createUseCase->execute($_POST, $_FILES['foto'] ?? []);
                return $this->jsonResponse(['ok' => true]);
            }

            if ($actualMethod === 'PUT' && $id) {
                $this->updateUseCase->execute($id, $_POST, $_FILES['foto'] ?? null);
                return $this->jsonResponse(['ok' => true]);
            }

            if ($actualMethod === 'DELETE' && $id) {
                $this->deleteUseCase->execute($id);
                return $this->jsonResponse(['ok' => true]);
            }

            return $this->jsonError('Ruta no encontrada para exponentes', 404);

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
