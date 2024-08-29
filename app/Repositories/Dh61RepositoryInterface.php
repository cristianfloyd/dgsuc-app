<?php
namespace App\Repositories;

use App\Models\Dh11;
use App\Models\Dh61;

interface Dh61RepositoryInterface
{
    public function createHistoricalRecord(Dh11 $category): void;
}
