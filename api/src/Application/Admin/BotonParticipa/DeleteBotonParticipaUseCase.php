<?php

namespace App\Application\Admin\BotonParticipa;

use App\Domain\Interfaces\BotonParticipaRepositoryInterface;
use Exception;

class DeleteBotonParticipaUseCase
{
    private $repository;

    public function __construct(BotonParticipaRepositoryInterface $repository)
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
