<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Interfaces\ContactRepositoryInterface;
use App\Infrastructure\Models\MensajeContacto;

class EloquentContactRepository implements ContactRepositoryInterface
{
    public function save(array $data): bool
    {
        return (bool) MensajeContacto::create($data);
    }
}
