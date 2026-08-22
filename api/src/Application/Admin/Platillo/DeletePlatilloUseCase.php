<?php

namespace App\Application\Admin\Platillo;

use App\Domain\Interfaces\PlatilloRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class DeletePlatilloUseCase
{
    private $repository;
    private $storageService;

    public function __construct(PlatilloRepositoryInterface $repository, StorageServiceInterface $storageService)
    {
        $this->repository = $repository;
        $this->storageService = $storageService;
    }

    public function execute(int $id): void
    {
        $current = $this->repository->getById($id);
        if (!$current) throw new Exception("Registro no encontrado");

        if ($current['imagen']) {
            $this->storageService->deleteImage('platillos_fotos', $current['imagen']);
        }

        $oldOrden = (int) $current['orden'];
        $this->repository->delete($id);
        $this->repository->shiftForDelete($oldOrden);
    }
}
