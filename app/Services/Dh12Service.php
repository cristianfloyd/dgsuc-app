<?php

namespace App\Services;

use App\Models\Dh12;

class Dh12Service
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected \App\Models\Dh12 $dh12)
    {
    }

    public static function getConceptosParaSelect()
    {
        return Dh12::query()->select('codn_conce', 'desc_conce')
            ->orderBy('codn_conce')
            ->get()
            ->mapWithKeys(fn($item): array => [$item->codn_conce => "{$item->codn_conce} - {$item->desc_conce}"])
            ->all();
    }
}
