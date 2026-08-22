<?php

namespace App\Application\Admin\MenuNav;

use App\Domain\Interfaces\MenuNavRepositoryInterface;
use Exception;

class UpdateMenuNavUseCase
{
    private $repository;

    public function __construct(MenuNavRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, array $data): void
    {
        if (empty($data['etiqueta'])) {
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
            'etiqueta' => isset($data['etiqueta']) ? $data['etiqueta'] : null,
            'enlace' => isset($data['enlace']) ? $data['enlace'] : null,
            'activo' => isset($data['activo']) ? $data['activo'] : null,
            'color' => isset($data['color']) ? $data['color'] : null,
            'orden' => $orden
        ];


        $this->repository->update($id, $updateData);
    }
}
