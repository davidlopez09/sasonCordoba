<?php

namespace App\Application\Admin\Footer;

use App\Domain\Interfaces\FooterRepositoryInterface;
use Exception;

class DeleteFooterUseCase
{
    private $repository;

    public function __construct(FooterRepositoryInterface $repository)
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
