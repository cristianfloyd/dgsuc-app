<?php

namespace App\Contracts;

interface OrigenRepositoryInterface
{
    public function findById(int $id);
}
