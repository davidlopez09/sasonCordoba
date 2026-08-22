<?php

namespace App\Application\Admin\Directorio;

use App\Domain\Interfaces\DirectorioRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class UpdateDirectorioUseCase
{
    private $repository;
    private $storageService;

    public function __construct(DirectorioRepositoryInterface $repository, StorageServiceInterface $storageService)
    {
        $this->repository = $repository;
        $this->storageService = $storageService;
    }

    public function execute(int $id, array $data, ?array $fileInfo): void
    {
        if (empty($data['nombre'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $current = $this->repository->getById($id);
        if (!$current) throw new Exception("Registro no encontrado");

        $fotoUrl = null;
        if ($fileInfo && isset($fileInfo['error']) && $fileInfo['error'] !== UPLOAD_ERR_NO_FILE) {
            $fotoUrl = $this->storageService->uploadImage('logos_directorio', 'directorio', $fileInfo);
            if ($fotoUrl && $current['logo']) {
                $this->storageService->deleteImage('logos_directorio', $current['logo']);
            }
        }

        $oldOrden = (int) $current['orden'];
        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : $oldOrden;
        $orden = max(1, min($count, $requestedOrden));

        $this->repository->shiftForUpdate($oldOrden, $orden);

        $updateData = [
            'nombre' => isset($data['nombre']) ? $data['nombre'] : null,
            'categoria' => isset($data['categoria']) ? $data['categoria'] : null,
            'descripcion' => isset($data['descripcion']) ? $data['descripcion'] : null,
            'contacto' => isset($data['contacto']) ? $data['contacto'] : null,
            'activo' => isset($data['activo']) ? $data['activo'] : null,
            'color' => isset($data['color']) ? $data['color'] : null,
            'orden' => $orden
        ];

        if ($fotoUrl) $updateData['logo'] = $fotoUrl;

        $this->repository->update($id, $updateData);
    }
}
