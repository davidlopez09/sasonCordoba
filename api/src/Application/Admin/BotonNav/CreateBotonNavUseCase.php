<?php

namespace App\Application\Admin\BotonNav;

use App\Domain\Interfaces\BotonNavRepositoryInterface;
use Exception;

class CreateBotonNavUseCase
{
    private $repository;

    public function __construct(BotonNavRepositoryInterface $repository)
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
            'enlace' => isset($data['enlace']) ? $data['enlace'] : null,
            'color_fondo' => isset($data['color_fondo']) ? $data['color_fondo'] : null,
            'color_texto' => isset($data['color_texto']) ? $data['color_texto'] : null,
            'color_borde' => isset($data['color_borde']) ? $data['color_borde'] : null,
            'activo' => isset($data['activo']) ? $data['activo'] : null,
            'orden' => $orden
        ]);
    }
}
