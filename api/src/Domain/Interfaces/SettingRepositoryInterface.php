<?php

namespace App\Domain\Interfaces;

interface SettingRepositoryInterface
{
    public function getSetting(string $table): ?array;
    public function updateSetting(string $table, array $data): void;
}
