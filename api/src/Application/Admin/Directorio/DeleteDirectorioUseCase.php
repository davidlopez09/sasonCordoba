<?php

namespace App\Application\Admin\Directorio;

use App\Domain\Interfaces\DirectorioRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class DeleteDirectorioUseCase
{
    private $repository;
    private $storageService;

    public function __construct(DirectorioRepositoryInterface $repository, StorageServiceInterface $storageService)
    {
        $this->repository = $repository;
        $this->storageService = $storageService;
    }

    public function execute(int $id): void
    {
        $current = $this->repository->getById($id);
        if (!$current) throw new Exception("Registro no encontrado");

        if ($current['logo']) {
            $this->storageService->deleteImage('logos_directorio', $current['logo']);
        }

        $oldOrden = (int) $current['orden'];
        $this->repository->delete($id);
        $this->repository->shiftForDelete($oldOrden);
    }
}
