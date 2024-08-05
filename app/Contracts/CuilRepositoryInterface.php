<?php

namespace App\Contracts;

interface CuilRepositoryInterface
{
    public function getCuilsNotInAfip(int $perPage = 10);
    public function getCuilsNoEncontrados();
}
