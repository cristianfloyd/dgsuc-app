<?php

namespace App\Services\Mapuche;

use App\Repositories\Mapuche\Dh21hRepository;

class Dh21hService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly Dh21hRepository $repository,
    ) {
    }
}
