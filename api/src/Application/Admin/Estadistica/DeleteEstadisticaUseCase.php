<?php

namespace App\Application\Admin\Estadistica;

use App\Domain\Interfaces\EstadisticaRepositoryInterface;
use Exception;

class DeleteEstadisticaUseCase
{
    private $repository;

    public function __construct(EstadisticaRepositoryInterface $repository)
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
