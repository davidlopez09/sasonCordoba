<?php

namespace App\Application\Admin\Slide;

use App\Domain\Interfaces\SlideRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class UpdateSlideUseCase
{
    private $repository;
    private $storageService;

    public function __construct(SlideRepositoryInterface $repository, StorageServiceInterface $storageService)
    {
        $this->repository = $repository;
        $this->storageService = $storageService;
    }

    public function execute(int $id, array $data, ?array $fileInfo): void
    {
        if (false) {
            throw new Exception("Faltan campos obligatorios");
        }

        $current = $this->repository->getById($id);
        if (!$current) throw new Exception("Registro no encontrado");

        $fotoUrl = null;
        if ($fileInfo && isset($fileInfo['error']) && $fileInfo['error'] !== UPLOAD_ERR_NO_FILE) {
            $fotoUrl = $this->storageService->uploadImage('slides', 'slide', $fileInfo);
            if ($fotoUrl && $current['imagen']) {
                $this->storageService->deleteImage('slides', $current['imagen']);
            }
        }

        $oldOrden = (int) $current['orden'];
        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : $oldOrden;
        $orden = max(1, min($count, $requestedOrden));

        $this->repository->shiftForUpdate($oldOrden, $orden);

        $updateData = [
            'activo' => isset($data['activo']) ? $data['activo'] : null,
            'orden' => $orden
        ];

        if ($fotoUrl) $updateData['imagen'] = $fotoUrl;

        $this->repository->update($id, $updateData);
    }
}
