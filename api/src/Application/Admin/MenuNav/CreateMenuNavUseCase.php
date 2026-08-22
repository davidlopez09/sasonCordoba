<?php

namespace App\Application\Admin\MenuNav;

use App\Domain\Interfaces\MenuNavRepositoryInterface;
use Exception;

class CreateMenuNavUseCase
{
    private $repository;

    public function __construct(MenuNavRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data): void
    {
        if (empty($data['etiqueta'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'etiqueta' => isset($data['etiqueta']) ? $data['etiqueta'] : null,
            'enlace' => isset($data['enlace']) ? $data['enlace'] : null,
            'activo' => isset($data['activo']) ? $data['activo'] : null,
            'color' => isset($data['color']) ? $data['color'] : null,
            'orden' => $orden
        ]);
    }
}
