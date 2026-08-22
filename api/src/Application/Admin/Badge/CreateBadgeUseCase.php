<?php

namespace App\Application\Admin\Badge;

use App\Domain\Interfaces\BadgeRepositoryInterface;
use Exception;

class CreateBadgeUseCase
{
    private $repository;

    public function __construct(BadgeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data): void
    {
        if (empty($data['texto'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'texto' => isset($data['texto']) ? $data['texto'] : null,
            'color' => isset($data['color']) ? $data['color'] : null,
            'color_fondo' => isset($data['color_fondo']) ? $data['color_fondo'] : null,
            'orden' => $orden
        ]);
    }
}
