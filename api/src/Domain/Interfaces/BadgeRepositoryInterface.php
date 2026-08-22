<?php

namespace App\Domain\Interfaces;

interface BadgeRepositoryInterface
{
    public function getAll(): array;
    public function getById(int $id): ?array;
    public function create(array $data): void;
    public function update(int $id, array $data): void;
    public function delete(int $id): void;
    public function getCount(): int;
    public function shiftForInsert(int $orden): void;
    public function shiftForUpdate(int $oldOrden, int $newOrden): void;
    public function shiftForDelete(int $orden): void;
}
