<?php

namespace App\Repositories;

interface AfipRelacionesActivasRepositoryInterface
{
    public function findByCuil(string $cuil);
}
