<?php

namespace App\Application\Admin\Export;

use App\Domain\Interfaces\VisitorRepositoryInterface;

class ExportVisitorsUseCase
{
    private $repository;

    public function __construct(VisitorRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(): array
    {
        $rows = $this->repository->getAll();
        
        $csvData = [];
        $cols = ['id', 'nombre', 'correo', 'telefono', 'created_at'];
        
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
