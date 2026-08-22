<?php

namespace App\Application\Admin\Itinerario;

use App\Domain\Interfaces\ItinerarioRepositoryInterface;
use Exception;

class DeleteItinerarioUseCase
{
    private $repository;

    public function __construct(ItinerarioRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $id): void
    {
        $current = $this->repository->getById($id);
        if (!$current) throw new Exception("Registro no encontrado");


        $oldOrden = (int) $current['orden'];
        $this->repository->delete($id);
        $this->repository->shiftForDelete($oldOrden);
    }
}
