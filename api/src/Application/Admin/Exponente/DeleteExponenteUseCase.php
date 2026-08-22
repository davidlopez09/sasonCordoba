<?php

namespace App\Application\Admin\Exponente;

use App\Domain\Interfaces\ExponenteRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class DeleteExponenteUseCase
{
    private $repository;
    private $storageService;

    public function __construct(ExponenteRepositoryInterface $repository, StorageServiceInterface $storageService)
    {
        $this->repository = $repository;
        $this->storageService = $storageService;
    }

    public function execute(int $id): void
    {
        $current = $this->repository->getById($id);
        if (!$current) {
            throw new Exception("Exponente no encontrado");
        }

        if ($current['foto']) {
            $this->storageService->deleteImage('exponentes_fotos', $current['foto']);
        }

        $oldOrden = (int) $current['orden'];
        $this->repository->delete($id);
        $this->repository->shiftForDelete($oldOrden);
    }
}
