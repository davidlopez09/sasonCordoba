<?php

namespace App\Application\Admin\BotonNav;

use App\Domain\Interfaces\BotonNavRepositoryInterface;
use Exception;

class UpdateBotonNavUseCase
{
    private $repository;

    public function __construct(BotonNavRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, array $data): void
    {
        if (empty($data['texto'])) {
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
            'texto' => isset($data['texto']) ? $data['texto'] : null,
            'enlace' => isset($data['enlace']) ? $data['enlace'] : null,
            'color_fondo' => isset($data['color_fondo']) ? $data['color_fondo'] : null,
            'color_texto' => isset($data['color_texto']) ? $data['color_texto'] : null,
            'color_borde' => isset($data['color_borde']) ? $data['color_borde'] : null,
            'activo' => isset($data['activo']) ? $data['activo'] : null,
            'orden' => $orden
        ];


        $this->repository->update($id, $updateData);
    }
}
