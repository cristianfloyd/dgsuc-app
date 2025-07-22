<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait para manejar scopes relacionados con unidades académicas.
 */
trait HasUacadScope
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {

    }

    /**
     * Scope para filtrar por unidad académica.
     *
     * @param Builder $query
     * @param string $codcUacad
     *
     * @return Builder
     */
    public function scopeByUacad(Builder $query, string $codcUacad): Builder
    {
        return $query->where('codc_uacad', $codcUacad);
    }
}
