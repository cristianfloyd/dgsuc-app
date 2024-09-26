<?php

namespace App\Services;

use App\Models\Dh12;

class Dh12Service
{
    protected $dh12;


    /**
     * Create a new class instance.
     */
    public function __construct(Dh12 $dh12)
    {
        $this->dh12 = $dh12;
    }

    public static function getConceptosParaSelect()
    {
        return Dh12::select('codn_conce', 'desc_conce')
            ->whereRaw('codn_conce/100 IN (1,2)')
            ->orderBy('codn_conce')
            ->get()
            ->pluck('desc_conce', 'codn_conce')
            ->toArray();
    }
}
