<?php

namespace App\Application\Admin\Faq;

use App\Domain\Interfaces\FaqRepositoryInterface;
use Exception;

class DeleteFaqUseCase
{
    private $repository;

    public function __construct(FaqRepositoryInterface $repository)
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
