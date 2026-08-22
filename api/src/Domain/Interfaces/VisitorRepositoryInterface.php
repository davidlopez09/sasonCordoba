<?php

namespace App\Domain\Interfaces;

interface VisitorRepositoryInterface
{
    public function save(array $data): bool;
    public function getAll(): array;
}
