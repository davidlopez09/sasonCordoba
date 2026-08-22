<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Interfaces\SettingRepositoryInterface;
use Illuminate\Database\Capsule\Manager as DB;

class EloquentSettingRepository implements SettingRepositoryInterface
{
    public function getSetting(string $table): ?array
    {
        $result = DB::table($table)->where('id', 1)->first();
        return $result ? (array)$result : null;
    }

    public function updateSetting(string $table, array $data): void
    {
        $exists = DB::table($table)->where('id', 1)->exists();
        if ($exists) {
            DB::table($table)->where('id', 1)->update($data);
        } else {
            $data['id'] = 1;
            DB::table($table)->insert($data);
        }
    }
}
