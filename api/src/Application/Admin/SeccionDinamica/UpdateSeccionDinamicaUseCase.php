<?php

namespace App\Application\Admin\SeccionDinamica;

use App\Domain\Interfaces\SeccionDinamicaRepositoryInterface;
use Exception;

class UpdateSeccionDinamicaUseCase
{
    private $repository;

    public function __construct(SeccionDinamicaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, array $data): void
    {
        if (empty($data['nombre'])) {
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
            'nombre' => isset($data['nombre']) ? $data['nombre'] : null,
            'insertar_despues' => isset($data['insertar_despues']) ? $data['insertar_despues'] : null,
            'activo' => isset($data['activo']) ? $data['activo'] : null,
            'orden' => $orden
        ];


        $this->repository->update($id, $updateData);
    }
}
