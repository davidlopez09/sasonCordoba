<?php

namespace App\Application\Admin\Itinerario;

use App\Domain\Interfaces\ItinerarioRepositoryInterface;
use Exception;

class UpdateItinerarioUseCase
{
    private $repository;

    public function __construct(ItinerarioRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, array $data): void
    {
        if (empty($data['titulo'])) {
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
        ];


        $this->repository->update($id, $updateData);
    }
}
