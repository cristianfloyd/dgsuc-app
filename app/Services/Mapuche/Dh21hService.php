<?php

namespace App\Services\Mapuche;

use App\Contracts\Mapuche\Dh21hRepositoryInterface;

class Dh21hService
{
    public function __construct(
        protected Dh21hRepositoryInterface $repository,
    ) {}
}
