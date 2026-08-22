<?php

namespace App\Application\Admin\Caracteristica;

use App\Domain\Interfaces\CaracteristicaRepositoryInterface;
use Exception;

class UpdateCaracteristicaUseCase
{
    private $repository;

    public function __construct(CaracteristicaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, array $data): void
    {
        if (empty($data['titulo'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $current = $this->repository->getById($id);
        if (!$current) throw new Exception("Registro no encontrado");


        $oldOrden = (int) $current['orden'];
        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : $oldOrden;
        $orden = max(1, min($count, $requestedOrden));

        $this->repository->shiftForUpdate($oldOrden, $orden);

        $updateData = [
            'icono' => isset($data['icono']) ? $data['icono'] : null,
            'titulo' => isset($data['titulo']) ? $data['titulo'] : null,
            'descripcion' => isset($data['descripcion']) ? $data['descripcion'] : null,
            'color' => isset($data['color']) ? $data['color'] : null,
            'orden' => $orden
        ];


        $this->repository->update($id, $updateData);
    }
}
