<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Interfaces\ItinerarioRepositoryInterface;
use Illuminate\Database\Capsule\Manager as DB;

class EloquentItinerarioRepository implements ItinerarioRepositoryInterface
{
    private $table = 'itinerario_items';

    public function getAll(): array
    {
        return DB::table($this->table)->orderBy('orden')->get()->toArray();
    }

    public function getById(int $id): ?array
    {
        $result = DB::table($this->table)->where('id', $id)->first();
        return $result ? (array)$result : null;
    }

    public function create(array $data): void
    {
        DB::table($this->table)->insert($data);
    }

    public function update(int $id, array $data): void
    {
        DB::table($this->table)->where('id', $id)->update($data);
    }

    public function delete(int $id): void
    {
        DB::table($this->table)->where('id', $id)->delete();
    }

    public function getCount(): int
    {
        return DB::table($this->table)->count();
    }

    public function shiftForInsert(int $orden): void
    {
        DB::table($this->table)->where('orden', '>=', $orden)->increment('orden');
    }

    public function shiftForUpdate(int $oldOrden, int $newOrden): void
    {
        if ($newOrden === $oldOrden) return;
        if ($newOrden < $oldOrden) {
            DB::table($this->table)->where('orden', '>=', $newOrden)->where('orden', '<', $oldOrden)->increment('orden');
        } else {
            DB::table($this->table)->where('orden', '>', $oldOrden)->where('orden', '<=', $newOrden)->decrement('orden');
        }
    }

    public function shiftForDelete(int $orden): void
    {
        DB::table($this->table)->where('orden', '>', $orden)->decrement('orden');
    }
}
