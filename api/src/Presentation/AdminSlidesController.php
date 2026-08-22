<?php

namespace App\Presentation;

use App\Application\Admin\Slide\CreateSlideUseCase;
use App\Application\Admin\Slide\UpdateSlideUseCase;
use App\Application\Admin\Slide\DeleteSlideUseCase;
use App\Domain\Interfaces\SlideRepositoryInterface;
use Exception;

class AdminSlidesController
{
    private $createUseCase;
    private $updateUseCase;
    private $deleteUseCase;
    private $repository;

    public function __construct(
        CreateSlideUseCase $createUseCase,
        UpdateSlideUseCase $updateUseCase,
        DeleteSlideUseCase $deleteUseCase,
        SlideRepositoryInterface $repository
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
                $this->createUseCase->execute($_POST, $_FILES['imagen'] ?? []);
                return $this->jsonResponse(['ok' => true]);
            }
            if ($actualMethod === 'PUT' && $id) {
                $this->updateUseCase->execute($id, $_POST, $_FILES['imagen'] ?? null);
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
