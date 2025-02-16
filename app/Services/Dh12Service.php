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
            ->orderBy('codn_conce')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->codn_conce => "{$item->codn_conce} - {$item->desc_conce}"];
            })
            ->toArray();
    }
}
