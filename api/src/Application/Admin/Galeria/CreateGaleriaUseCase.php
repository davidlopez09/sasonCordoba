<?php

namespace App\Application\Admin\Galeria;

use App\Domain\Interfaces\GaleriaRepositoryInterface;
use Exception;

class CreateGaleriaUseCase
{
    private $repository;

    public function __construct(GaleriaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data): void
    {
        if (empty($data['tipo']) || empty($data['url'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'tipo' => isset($data['tipo']) ? $data['tipo'] : null,
            'url' => isset($data['url']) ? $data['url'] : null,
            'titulo' => isset($data['titulo']) ? $data['titulo'] : null,
            'edicion' => isset($data['edicion']) ? $data['edicion'] : null,
            'activo' => isset($data['activo']) ? $data['activo'] : null,
            'orden' => $orden
        ]);
    }
}
