<?php

namespace App\Application\Admin\BotonParticipa;

use App\Domain\Interfaces\BotonParticipaRepositoryInterface;
use Exception;

class UpdateBotonParticipaUseCase
{
    private $repository;

    public function __construct(BotonParticipaRepositoryInterface $repository)
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
