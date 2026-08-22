<?php

namespace App\Application\Admin\Patrocinador;

use App\Domain\Interfaces\PatrocinadorRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class DeletePatrocinadorUseCase
{
    private $repository;
    private $storageService;

    public function __construct(PatrocinadorRepositoryInterface $repository, StorageServiceInterface $storageService)
    {
        $this->repository = $repository;
        $this->storageService = $storageService;
    }

    public function execute(int $id): void
    {
        $current = $this->repository->getById($id);
        if (!$current) throw new Exception("Registro no encontrado");

        if ($current['logo']) {
            $this->storageService->deleteImage('logo_patrocinador', $current['logo']);
        }

        $oldOrden = (int) $current['orden'];
        $this->repository->delete($id);
        $this->repository->shiftForDelete($oldOrden);
    }
}
