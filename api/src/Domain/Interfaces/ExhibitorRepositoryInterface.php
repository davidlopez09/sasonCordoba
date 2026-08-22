<?php

namespace App\Domain\Interfaces;

interface ExhibitorRepositoryInterface
{
    public function save(array $data): bool;
    public function getAll(): array;
}
