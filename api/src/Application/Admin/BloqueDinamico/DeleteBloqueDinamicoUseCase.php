<?php

namespace App\Application\Admin\BloqueDinamico;

use App\Domain\Interfaces\BloqueDinamicoRepositoryInterface;
use Exception;

class DeleteBloqueDinamicoUseCase
{
    private $repository;

    public function __construct(BloqueDinamicoRepositoryInterface $repository)
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
