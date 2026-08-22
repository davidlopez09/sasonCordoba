<?php

namespace App\Application\Admin\SeccionDinamica;

use App\Domain\Interfaces\SeccionDinamicaRepositoryInterface;
use Exception;

class DeleteSeccionDinamicaUseCase
{
    private $repository;

    public function __construct(SeccionDinamicaRepositoryInterface $repository)
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
