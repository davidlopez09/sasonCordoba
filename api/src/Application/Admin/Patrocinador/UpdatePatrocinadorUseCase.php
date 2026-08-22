<?php

namespace App\Application\Admin\Patrocinador;

use App\Domain\Interfaces\PatrocinadorRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class UpdatePatrocinadorUseCase
{
    private $repository;
    private $storageService;

    public function __construct(PatrocinadorRepositoryInterface $repository, StorageServiceInterface $storageService)
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
            $fotoUrl = $this->storageService->uploadImage('logo_patrocinador', 'patrocinador', $fileInfo);
            if ($fotoUrl && $current['logo']) {
                $this->storageService->deleteImage('logo_patrocinador', $current['logo']);
            }
        }

        $oldOrden = (int) $current['orden'];
        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : $oldOrden;
        $orden = max(1, min($count, $requestedOrden));

        $this->repository->shiftForUpdate($oldOrden, $orden);

        $updateData = [
            'nombre' => !empty($data['nombre']) ? $data['nombre'] : null,
            'url' => !empty($data['url']) ? $data['url'] : null,
            'orden' => $orden
        ];

        if ($fotoUrl) $updateData['logo'] = $fotoUrl;

        $this->repository->update($id, $updateData);
    }
}
