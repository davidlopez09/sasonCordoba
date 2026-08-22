<?php

namespace App\Application\Admin\Faq;

use App\Domain\Interfaces\FaqRepositoryInterface;
use Exception;

class CreateFaqUseCase
{
    private $repository;

    public function __construct(FaqRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(array $data): void
    {
        if (empty($data['pregunta']) || empty($data['respuesta'])) {
            throw new Exception("Faltan campos obligatorios");
        }

        $count = $this->repository->getCount();
        $requestedOrden = isset($data['orden']) ? (int)$data['orden'] : ($count + 1);
        $orden = max(1, min($count + 1, $requestedOrden));

        $this->repository->shiftForInsert($orden);

        $this->repository->create([
            'pregunta' => isset($data['pregunta']) ? $data['pregunta'] : null,
            'respuesta' => isset($data['respuesta']) ? $data['respuesta'] : null,
            'activo' => isset($data['activo']) ? $data['activo'] : null,
            'color' => isset($data['color']) ? $data['color'] : null,
            'orden' => $orden
        ]);
    }
}
