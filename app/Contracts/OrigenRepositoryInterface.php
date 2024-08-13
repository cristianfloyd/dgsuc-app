<?php

namespace App\Contracts;

use App\Models\OrigenesModel;

interface OrigenRepositoryInterface
{
    public function findById(int $id): ?OrigenesModel;
    public function findByName(string $name): ?OrigenesModel;
}
