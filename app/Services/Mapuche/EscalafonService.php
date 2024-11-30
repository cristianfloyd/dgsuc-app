<?php

namespace App\Services\Mapuche;

use App\Models\Dh89;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class EscalafonService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getEscalafones()
    {
        try {
            return Dh89::select('descesc', 'dh11.codigoescalafon')
            ->distinct()
            ->join('mapuche.dh11', 'dh89.codigoescalafon', '=', 'dh11.codigoescalafon')
            ->pluck('descesc', 'codigoescalafon');
        } catch (QueryException $e) {
            Log::error('Error al obtener los escalafones: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
