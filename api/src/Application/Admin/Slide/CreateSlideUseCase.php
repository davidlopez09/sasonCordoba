<?php

namespace App\Application\Admin\Slide;

use App\Domain\Interfaces\SlideRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class CreateSlideUseCase
{
    private $repository;
    private $storageService;

    public function __construct(SlideRepositoryInterface $repository, StorageServiceInterface $storageService)
    {
        $this->repository = $repository;
        $this->storageService = $storageService;
    }

    public function execute(array $data, array $fileInfo): void
    {
        if (false) {
            throw new Exception("Faltan campos obligatorios");
        }

        $fotoUrl = $this->storageService->uploadImage('slides', 'slide', $fileInfo);
        if (!$fotoUrl) throw new Exception('Debés subir una imagen/logo');

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'imagen' => $fotoUrl,
            'activo' => isset($data['activo']) ? $data['activo'] : null,
            'orden' => $orden
        ]);
    }
}
