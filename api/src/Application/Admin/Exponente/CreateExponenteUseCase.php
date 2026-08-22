<?php

namespace App\Application\Admin\Exponente;

use App\Domain\Interfaces\ExponenteRepositoryInterface;
use App\Domain\Interfaces\StorageServiceInterface;
use Exception;

class CreateExponenteUseCase
{
    private $repository;
    private $storageService;

    public function __construct(ExponenteRepositoryInterface $repository, StorageServiceInterface $storageService)
    {
        $this->repository = $repository;
        $this->storageService = $storageService;
    }

    public function execute(array $data, array $fileInfo): void
    {
        if (empty($data['nombre']) || empty($data['especialidad'])) {
            throw new Exception("Nombre y especialidad son obligatorios");
        }

        $fotoUrl = $this->storageService->uploadImage('exponentes_fotos', 'exponente', $fileInfo);
        
        if (!$fotoUrl) {
            throw new Exception('Debés subir una foto');
        }

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'nombre' => $data['nombre'],
            'especialidad' => $data['especialidad'],
            'foto' => $fotoUrl,
            'instagram_url' => !empty($data['instagram_url']) ? $data['instagram_url'] : null,
            'twitter_url' => !empty($data['twitter_url']) ? $data['twitter_url'] : null,
            'color' => !empty($data['color']) ? $data['color'] : '#1a1a1a',
            'orden' => $orden
        ]);
    }
}
