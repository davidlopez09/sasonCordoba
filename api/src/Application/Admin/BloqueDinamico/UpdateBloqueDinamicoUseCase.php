<?php

namespace App\Application\Admin\BloqueDinamico;

use App\Domain\Interfaces\BloqueDinamicoRepositoryInterface;
use Exception;

class UpdateBloqueDinamicoUseCase
{
    private $repository;

    public function __construct(BloqueDinamicoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, array $data): void
    {
        if (empty($data['seccion_id']) || empty($data['tipo'])) {
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
            'seccion_id' => isset($data['seccion_id']) ? $data['seccion_id'] : null,
            'tipo' => isset($data['tipo']) ? $data['tipo'] : null,
            'posicion' => isset($data['posicion']) ? $data['posicion'] : null,
            'contenido' => isset($data['contenido']) ? $data['contenido'] : null,
            'orden' => $orden
        ];


        $this->repository->update($id, $updateData);
    }
}
