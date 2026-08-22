<?php

namespace App\Application\Admin\Galeria;

use App\Domain\Interfaces\GaleriaRepositoryInterface;
use Exception;

class DeleteGaleriaUseCase
{
    private $repository;

    public function __construct(GaleriaRepositoryInterface $repository)
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
