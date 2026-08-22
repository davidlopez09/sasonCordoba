<?php

namespace App\Application\Admin\Galeria;

use App\Domain\Interfaces\GaleriaRepositoryInterface;
use Exception;

class UpdateGaleriaUseCase
{
    private $repository;

    public function __construct(GaleriaRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, array $data): void
    {
        if (empty($data['tipo']) || empty($data['url'])) {
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
            'tipo' => isset($data['tipo']) ? $data['tipo'] : null,
            'url' => isset($data['url']) ? $data['url'] : null,
            'titulo' => isset($data['titulo']) ? $data['titulo'] : null,
            'edicion' => isset($data['edicion']) ? $data['edicion'] : null,
            'activo' => isset($data['activo']) ? $data['activo'] : null,
            'orden' => $orden
        ];


        $this->repository->update($id, $updateData);
    }
}
