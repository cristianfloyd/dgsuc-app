<?php

namespace App\Contracts\Repositories;

use App\Data\DataObjects\Dhr3Data;
use App\Models\Mapuche\Dhr3;
use Illuminate\Database\Eloquent\Collection;

interface Dhr3RepositoryInterface
{
    public function find(int $nro_liqui, int $nro_legaj, int $nro_cargo, string $codc_hhdd, int $nro_renglo): ?Dhr3;

    public function all(): Collection;

    public function create(Dhr3Data $data): Dhr3;

    public function update(int $nro_liqui, int $nro_legaj, int $nro_cargo, string $codc_hhdd, int $nro_renglo, Dhr3Data $data): bool;

    public function delete(int $nro_liqui, int $nro_legaj, int $nro_cargo, string $codc_hhdd, int $nro_renglo): bool;

    public function findByLiquidacion(int $nro_liqui): Collection;

    public function findByLegajo(int $nro_legaj): Collection;

    public function findByConcepto(int $nro_conce): Collection;
}
