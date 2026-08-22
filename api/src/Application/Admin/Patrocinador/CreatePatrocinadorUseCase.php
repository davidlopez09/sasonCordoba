<?php

namespace App\Application\Admin\Patrocinador;

use App\Domain\Interfaces\PatrocinadorRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class CreatePatrocinadorUseCase
{
    private $repository;
    private $storageService;

    public function __construct(PatrocinadorRepositoryInterface $repository, StorageServiceInterface $storageService)
    {
        $this->repository = $repository;
        $this->storageService = $storageService;
    }

    public function execute(array $data, array $fileInfo): void
    {
        if (empty($data['nombre'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $fotoUrl = $this->storageService->uploadImage('logo_patrocinador', 'patrocinador', $fileInfo);
        if (!$fotoUrl) throw new Exception('Debés subir una imagen/logo');

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'logo' => $fotoUrl,
            'nombre' => !empty($data['nombre']) ? $data['nombre'] : null,
            'url' => !empty($data['url']) ? $data['url'] : null,
            'orden' => $orden
        ]);
    }
}
