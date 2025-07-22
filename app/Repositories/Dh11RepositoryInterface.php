<?php

namespace App\Repositories;

use App\Models\Dh11;
use App\Models\Dh61;
use Illuminate\Database\Eloquent\Collection;

interface Dh11RepositoryInterface
{
    public function updateImppBasic(Dh11 $category, float $percentage, ?array $periodoFiscal = null): bool;

    public function getAllCurrentRecords(): Collection;

    public function update(array $attributes, Dh61 $values): bool;

    public function getAllCodcCateg(): array;

    public function all();

    public function find(string $codcCateg);

    public function create(array $data);

    public function delete(string $codcCateg);
}
