<?php

namespace App\Application\Admin\BotonHero;

use App\Domain\Interfaces\BotonHeroRepositoryInterface;
use Exception;

class DeleteBotonHeroUseCase
{
    private $repository;

    public function __construct(BotonHeroRepositoryInterface $repository)
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
