<?php

namespace App\Traits;

trait PostgresqlTrait
{
    public function freshTimestamp(): string
    {
        return now()->format('Y-m-d H:i:s.u');
    }

    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:s.u';
    }
}
