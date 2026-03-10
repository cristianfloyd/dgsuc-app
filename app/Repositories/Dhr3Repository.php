<?php

namespace App\Repositories;

use App\Data\DataObjects\Dhr3Data;
use App\Models\Mapuche\Dhr3;
use Illuminate\Database\Eloquent\Collection;

class Dhr3Repository
{
    public function __construct(
        private readonly Dhr3 $model,
    ) {
    }

    public function find(int $nro_liqui, int $nro_legaj, int $nro_cargo, string $codc_hhdd, int $nro_renglo): ?Dhr3
    {
        return $this->model->where([
            'nro_liqui' => $nro_liqui,
            'nro_legaj' => $nro_legaj,
            'nro_cargo' => $nro_cargo,
            'codc_hhdd' => $codc_hhdd,
            'nro_renglo' => $nro_renglo,
        ])->first();
    }

    public function findByPrimaryKey(int $nro_liqui, int $nro_legaj, int $nro_cargo, string $codc_hhdd, int $nro_renglo)
    {
        $record = $this->model->where('nro_liqui', $nro_liqui)
            ->where('nro_legaj', $nro_legaj)
            ->where('nro_cargo', $nro_cargo)
            ->where('codc_hhdd', $codc_hhdd)
            ->where('nro_renglo', $nro_renglo)
            ->first();
        return $record ? Dhr3Data::from($record) : null;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function create(Dhr3Data $data): Dhr3
    {
        return $this->model->create($data->toArray());
    }

    public function update(int $nro_liqui, int $nro_legaj, int $nro_cargo, string $codc_hhdd, int $nro_renglo, Dhr3Data $data): bool
    {
        $record = $this->find($nro_liqui, $nro_legaj, $nro_cargo, $codc_hhdd, $nro_renglo);
        return $record ? $record->update($data->toArray()) : false;
    }

    public function delete(int $nro_liqui, int $nro_legaj, int $nro_cargo, string $codc_hhdd, int $nro_renglo): bool
    {
        $record = $this->find($nro_liqui, $nro_legaj, $nro_cargo, $codc_hhdd, $nro_renglo);
        return $record ? $record->delete() : false;
    }

    public function findByLiquidacion(int $nro_liqui): Collection
    {
        return $this->model->where('nro_liqui', $nro_liqui)->get();
    }

    public function findByLegajo(int $nro_legaj): Collection
    {
        return $this->model->where('nro_legaj', $nro_legaj)->get();
    }

    public function findByConcepto(int $nro_conce): Collection
    {
        return $this->model->where('nro_conce', $nro_conce)->get();
    }
}
