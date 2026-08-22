<?php

namespace App\Application\Admin\Caracteristica;

use App\Domain\Interfaces\CaracteristicaRepositoryInterface;
use Exception;

class CreateCaracteristicaUseCase
{
    private $repository;

    public function __construct(CaracteristicaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data): void
    {
        if (empty($data['titulo'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'icono' => isset($data['icono']) ? $data['icono'] : null,
            'titulo' => isset($data['titulo']) ? $data['titulo'] : null,
            'descripcion' => isset($data['descripcion']) ? $data['descripcion'] : null,
            'color' => isset($data['color']) ? $data['color'] : null,
            'orden' => $orden
        ]);
    }
}
