<?php

namespace App\Application\Admin\BloqueDinamico;

use App\Domain\Interfaces\BloqueDinamicoRepositoryInterface;
use Exception;

class CreateBloqueDinamicoUseCase
{
    private $repository;

    public function __construct(BloqueDinamicoRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data): void
    {
        if (empty($data['seccion_id']) || empty($data['tipo'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'seccion_id' => isset($data['seccion_id']) ? $data['seccion_id'] : null,
            'tipo' => isset($data['tipo']) ? $data['tipo'] : null,
            'posicion' => isset($data['posicion']) ? $data['posicion'] : null,
            'contenido' => isset($data['contenido']) ? $data['contenido'] : null,
            'orden' => $orden
        ]);
    }
}
