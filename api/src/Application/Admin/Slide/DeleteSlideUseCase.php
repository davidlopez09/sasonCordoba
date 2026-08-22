<?php

namespace App\Application\Admin\Slide;

use App\Domain\Interfaces\SlideRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class DeleteSlideUseCase
{
    private $repository;
    private $storageService;

    public function __construct(SlideRepositoryInterface $repository, StorageServiceInterface $storageService)
    {
        $this->repository = $repository;
        $this->storageService = $storageService;
    }

    public function execute(int $id): void
    {
        $current = $this->repository->getById($id);
        if (!$current) throw new Exception("Registro no encontrado");

        if ($current['imagen']) {
            $this->storageService->deleteImage('slides', $current['imagen']);
        }

        $oldOrden = (int) $current['orden'];
        $this->repository->delete($id);
        $this->repository->shiftForDelete($oldOrden);
    }
}
