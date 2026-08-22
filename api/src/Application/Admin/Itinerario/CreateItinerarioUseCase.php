<?php

namespace App\Application\Admin\Itinerario;

use App\Domain\Interfaces\ItinerarioRepositoryInterface;
use Exception;

class CreateItinerarioUseCase
{
    private $repository;

    public function __construct(ItinerarioRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data): void
    {
        if (empty($data['titulo'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'hora' => !empty($data['hora']) ? $data['hora'] : null,
            'dia' => !empty($data['dia']) ? $data['dia'] : null,
            'titulo' => !empty($data['titulo']) ? $data['titulo'] : null,
            'nombre_chef' => !empty($data['nombre_chef']) ? $data['nombre_chef'] : null,
            'descripcion' => !empty($data['descripcion']) ? $data['descripcion'] : null,
            'tipo' => !empty($data['tipo']) ? $data['tipo'] : null,
            'color' => !empty($data['color']) ? $data['color'] : null,
            'color_fondo' => !empty($data['color_fondo']) ? $data['color_fondo'] : null,
            'color_borde' => !empty($data['color_borde']) ? $data['color_borde'] : null,
            'orden' => $orden
        ]);
    }
}
