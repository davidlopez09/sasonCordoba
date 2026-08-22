<?php

namespace App\Application\Admin\SeccionDinamica;

use App\Domain\Interfaces\SeccionDinamicaRepositoryInterface;
use Exception;

class CreateSeccionDinamicaUseCase
{
    private $repository;

    public function __construct(SeccionDinamicaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data): void
    {
        if (empty($data['nombre'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'nombre' => isset($data['nombre']) ? $data['nombre'] : null,
            'insertar_despues' => isset($data['insertar_despues']) ? $data['insertar_despues'] : null,
            'activo' => isset($data['activo']) ? $data['activo'] : null,
            'orden' => $orden
        ]);
    }
}
