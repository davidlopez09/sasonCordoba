<?php

namespace App\Application\Admin\Exponente;

use App\Domain\Interfaces\ExponenteRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class UpdateExponenteUseCase
{
    private $repository;
    private $storageService;

    public function __construct(ExponenteRepositoryInterface $repository, StorageServiceInterface $storageService)
    {
        $this->repository = $repository;
        $this->storageService = $storageService;
    }

    public function execute(int $id, array $data, ?array $fileInfo): void
    {
        if (empty($data['nombre']) || empty($data['especialidad'])) {
            throw new Exception("Nombre y especialidad son obligatorios");
        }

        $current = $this->repository->getById($id);
        if (!$current) {
            throw new Exception("Exponente no encontrado");
        }

        $fotoUrl = null;
        if ($fileInfo && isset($fileInfo['error']) && $fileInfo['error'] !== UPLOAD_ERR_NO_FILE) {
            $fotoUrl = $this->storageService->uploadImage('exponentes_fotos', 'exponente', $fileInfo);
            if ($fotoUrl && $current['foto']) {
                $this->storageService->deleteImage('exponentes_fotos', $current['foto']);
            }
        }

        $oldOrden = (int) $current['orden'];
        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : $oldOrden;
        $orden = max(1, min($count, $requestedOrden));

        $this->repository->shiftForUpdate($oldOrden, $orden);

        $updateData = [
            'nombre' => $data['nombre'],
            'especialidad' => $data['especialidad'],
            'instagram_url' => !empty($data['instagram_url']) ? $data['instagram_url'] : null,
            'twitter_url' => !empty($data['twitter_url']) ? $data['twitter_url'] : null,
            'color' => !empty($data['color']) ? $data['color'] : '#1a1a1a',
            'orden' => $orden
        ];

        if ($fotoUrl) {
            $updateData['foto'] = $fotoUrl;
        }

        $this->repository->update($id, $updateData);
    }
}
