<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Interfaces\VisitorRepositoryInterface;
use App\Infrastructure\Models\RegistroVisitante;

class EloquentVisitorRepository implements VisitorRepositoryInterface
{
    public function save(array $data): bool
    {
        return (bool) RegistroVisitante::create($data);
    }

    public function getAll(): array
    {
        return RegistroVisitante::orderBy('created_at', 'desc')->get()->toArray();
    }
}
