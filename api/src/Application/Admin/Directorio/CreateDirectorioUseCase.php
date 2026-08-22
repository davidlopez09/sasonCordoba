<?php

namespace App\Application\Admin\Directorio;

use App\Domain\Interfaces\DirectorioRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class CreateDirectorioUseCase
{
    private $repository;
    private $storageService;

    public function __construct(DirectorioRepositoryInterface $repository, StorageServiceInterface $storageService)
    {
        $this->repository = $repository;
        $this->storageService = $storageService;
    }

    public function execute(array $data, array $fileInfo): void
    {
        if (empty($data['nombre'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $fotoUrl = $this->storageService->uploadImage('logos_directorio', 'directorio', $fileInfo);
        if (!$fotoUrl) throw new Exception('Debés subir una imagen/logo');

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'logo' => $fotoUrl,
            'nombre' => isset($data['nombre']) ? $data['nombre'] : null,
            'categoria' => isset($data['categoria']) ? $data['categoria'] : null,
            'descripcion' => isset($data['descripcion']) ? $data['descripcion'] : null,
            'contacto' => isset($data['contacto']) ? $data['contacto'] : null,
            'activo' => isset($data['activo']) ? $data['activo'] : null,
            'color' => isset($data['color']) ? $data['color'] : null,
            'orden' => $orden
        ]);
    }
}
