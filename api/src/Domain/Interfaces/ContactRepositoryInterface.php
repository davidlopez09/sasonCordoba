<?php

namespace App\Domain\Interfaces;

interface ContactRepositoryInterface
{
    public function save(array $data): bool;
}
