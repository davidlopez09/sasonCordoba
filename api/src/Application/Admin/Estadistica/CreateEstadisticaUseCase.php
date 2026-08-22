<?php

namespace App\Application\Admin\Estadistica;

use App\Domain\Interfaces\EstadisticaRepositoryInterface;
use Exception;

class CreateEstadisticaUseCase
{
    private $repository;

    public function __construct(EstadisticaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data): void
    {
        if (empty($data['numero']) || empty($data['etiqueta'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'numero' => isset($data['numero']) ? $data['numero'] : null,
            'etiqueta' => isset($data['etiqueta']) ? $data['etiqueta'] : null,
            'icono' => isset($data['icono']) ? $data['icono'] : null,
            'color' => isset($data['color']) ? $data['color'] : null,
            'orden' => $orden
        ]);
    }
}
