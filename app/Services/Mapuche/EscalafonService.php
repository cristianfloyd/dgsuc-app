<?php

namespace App\Services\Mapuche;

use App\Models\Dh89;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class EscalafonService
{
    use MapucheConnectionTrait;

    public function getEscalafones()
    {
        try {
            return Dh89::query()->select('descesc', 'dh11.codigoescalafon')
                ->distinct()
                ->join('mapuche.dh11', 'dh89.codigoescalafon', '=', 'dh11.codigoescalafon')
                ->pluck('descesc', 'codigoescalafon');
        } catch (QueryException $e) {
            Log::error('Error al obtener los escalafones: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
