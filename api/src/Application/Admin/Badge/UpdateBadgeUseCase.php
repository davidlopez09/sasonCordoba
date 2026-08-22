<?php

namespace App\Application\Admin\Badge;

use App\Domain\Interfaces\BadgeRepositoryInterface;
use Exception;

class UpdateBadgeUseCase
{
    private $repository;

    public function __construct(BadgeRepositoryInterface $repository)
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
            'color' => isset($data['color']) ? $data['color'] : null,
            'color_fondo' => isset($data['color_fondo']) ? $data['color_fondo'] : null,
            'orden' => $orden
        ];


        $this->repository->update($id, $updateData);
    }
}
