<?php

namespace App\Repositories\Mapuche;

use App\Contracts\Mapuche\Repositories\Dh13RepositoryInterface;
use App\DTOs\Mapuche\Dh13\CreateDh13DTO;
use App\DTOs\Mapuche\Dh13\UpdateDh13DTO;
use App\Models\Dh13;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class Dh13Repository implements Dh13RepositoryInterface
{
    public function findByConcepto(int $codn_conce): Collection
    {
        return Dh13::where('codn_conce', $codn_conce)
            ->orderBy('nro_orden_formula')
            ->get();
    }

    public function create(CreateDh13DTO $data): Dh13
    {
        return DB::transaction(function () use ($data) {
            $validatedData = $data::validate($data);
            return Dh13::create($validatedData);
        });
    }

    public function update(UpdateDh13DTO $data, int $codn_conce, int $nro_orden_formula): bool
    {
        return DB::transaction(function () use ($data, $codn_conce, $nro_orden_formula) {
            return Dh13::where('codn_conce', $codn_conce)
                ->where('nro_orden_formula', $nro_orden_formula)
                ->update($data->getUpdateableFields());
        });
    }

    public function delete(int $codn_conce, int $nro_orden_formula): bool
    {
        return DB::transaction(function () use ($codn_conce, $nro_orden_formula) {
            return Dh13::where('codn_conce', $codn_conce)
                ->where('nro_orden_formula', $nro_orden_formula)
                ->delete();
        });
    }
}
