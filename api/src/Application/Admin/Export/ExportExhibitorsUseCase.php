<?php

namespace App\Application\Admin\Export;

use App\Domain\Interfaces\ExhibitorRepositoryInterface;

class ExportExhibitorsUseCase
{
    private $repository;

    public function __construct(ExhibitorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(): array
    {
        $rows = $this->repository->getAll();
        
        $csvData = [];
        $cols = ['id', 'nombre_empresa', 'categoria', 'nombre_contacto', 'correo', 'telefono', 'descripcion', 'created_at'];
        
        // Add headers
        $csvData[] = $cols;

        foreach ($rows as $row) {
            $csvData[] = array_map(function($c) use ($row) {
                return $row[$c] ?? '';
            }, $cols);
        }

        return $csvData;
    }
}
