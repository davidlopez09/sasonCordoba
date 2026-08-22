<?php

namespace App\Application\Admin\MenuNav;

use App\Domain\Interfaces\MenuNavRepositoryInterface;
use Exception;

class DeleteMenuNavUseCase
{
    private $repository;

    public function __construct(MenuNavRepositoryInterface $repository)
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
