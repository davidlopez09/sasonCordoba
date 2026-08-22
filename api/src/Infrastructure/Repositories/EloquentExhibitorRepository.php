<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Interfaces\ExhibitorRepositoryInterface;
use App\Infrastructure\Models\RegistroExpositor;

class EloquentExhibitorRepository implements ExhibitorRepositoryInterface
{
    public function save(array $data): bool
    {
        return (bool) RegistroExpositor::create($data);
    }

    public function getAll(): array
    {
        return RegistroExpositor::orderBy('created_at', 'desc')->get()->toArray();
    }
}
