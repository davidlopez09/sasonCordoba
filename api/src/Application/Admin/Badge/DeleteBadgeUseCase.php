<?php

namespace App\Application\Admin\Badge;

use App\Domain\Interfaces\BadgeRepositoryInterface;
use Exception;

class DeleteBadgeUseCase
{
    private $repository;

    public function __construct(BadgeRepositoryInterface $repository)
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
