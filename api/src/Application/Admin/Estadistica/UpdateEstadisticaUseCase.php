<?php

namespace App\Application\Admin\Estadistica;

use App\Domain\Interfaces\EstadisticaRepositoryInterface;
use Exception;

class UpdateEstadisticaUseCase
{
    private $repository;

    public function __construct(EstadisticaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, array $data): void
    {
        if (empty($data['numero']) || empty($data['etiqueta'])) {
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
            'numero' => isset($data['numero']) ? $data['numero'] : null,
            'etiqueta' => isset($data['etiqueta']) ? $data['etiqueta'] : null,
            'icono' => isset($data['icono']) ? $data['icono'] : null,
            'color' => isset($data['color']) ? $data['color'] : null,
            'orden' => $orden
        ];


        $this->repository->update($id, $updateData);
    }
}
