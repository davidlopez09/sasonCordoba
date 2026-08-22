<?php

namespace App\Application\Admin\Faq;

use App\Domain\Interfaces\FaqRepositoryInterface;
use Exception;

class UpdateFaqUseCase
{
    private $repository;

    public function __construct(FaqRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id, array $data): void
    {
        if (empty($data['pregunta']) || empty($data['respuesta'])) {
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
            'pregunta' => isset($data['pregunta']) ? $data['pregunta'] : null,
            'respuesta' => isset($data['respuesta']) ? $data['respuesta'] : null,
            'activo' => isset($data['activo']) ? $data['activo'] : null,
            'color' => isset($data['color']) ? $data['color'] : null,
            'orden' => $orden
        ];


        $this->repository->update($id, $updateData);
    }
}
