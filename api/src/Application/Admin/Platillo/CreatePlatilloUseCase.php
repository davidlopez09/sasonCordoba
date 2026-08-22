<?php

namespace App\Application\Admin\Platillo;

use App\Domain\Interfaces\PlatilloRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class CreatePlatilloUseCase
{
    private $repository;
    private $storageService;

    public function __construct(PlatilloRepositoryInterface $repository, StorageServiceInterface $storageService)
    {
        $this->repository = $repository;
        $this->storageService = $storageService;
    }

    public function execute(array $data, array $fileInfo): void
    {
        if (empty($data['nombre'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $fotoUrl = $this->storageService->uploadImage('platillos_fotos', 'platillo', $fileInfo);
        if (!$fotoUrl) throw new Exception('Debés subir una imagen/logo');

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'imagen' => $fotoUrl,
            'nombre' => !empty($data['nombre']) ? $data['nombre'] : null,
            'descripcion' => !empty($data['descripcion']) ? $data['descripcion'] : null,
            'color' => !empty($data['color']) ? $data['color'] : null,
            'etiqueta' => !empty($data['etiqueta']) ? $data['etiqueta'] : null,
            'orden' => $orden
        ]);
    }
}
