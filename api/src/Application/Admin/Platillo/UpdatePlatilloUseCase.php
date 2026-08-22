<?php

namespace App\Application\Admin\Platillo;

use App\Domain\Interfaces\PlatilloRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class UpdatePlatilloUseCase
{
    private $repository;
    private $storageService;

    public function __construct(PlatilloRepositoryInterface $repository, StorageServiceInterface $storageService)
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
            $fotoUrl = $this->storageService->uploadImage('platillos_fotos', 'platillo', $fileInfo);
            if ($fotoUrl && $current['imagen']) {
                $this->storageService->deleteImage('platillos_fotos', $current['imagen']);
            }
        }

        $oldOrden = (int) $current['orden'];
        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : $oldOrden;
        $orden = max(1, min($count, $requestedOrden));

        $this->repository->shiftForUpdate($oldOrden, $orden);

        $updateData = [
            'nombre' => !empty($data['nombre']) ? $data['nombre'] : null,
            'descripcion' => !empty($data['descripcion']) ? $data['descripcion'] : null,
            'color' => !empty($data['color']) ? $data['color'] : null,
            'etiqueta' => !empty($data['etiqueta']) ? $data['etiqueta'] : null,
            'orden' => $orden
        ];

        if ($fotoUrl) $updateData['imagen'] = $fotoUrl;

        $this->repository->update($id, $updateData);
    }
}
