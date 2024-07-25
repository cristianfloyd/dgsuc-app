<?php

namespace App\services;

use Illuminate\Support\Facades\Log;
use App\Models\AfipRelacionesActivas;

class DatabaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function insertarDatosMasivos(array $datosMapeados)
    {
        try {
            $resultado = AfipRelacionesActivas::insert($datosMapeados);
            if ($resultado) {
                Log::info('Se importaron los datos correctamente');
                return true;
            } else {
                Log::error('No se importaron los datos correctamente');
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error al insertar datos masivos: ' . $e->getMessage());
            return false;
        }
    }

    public function mapearDatosAlModelo(array $linea)
    {
        return AfipRelacionesActivas::mapearDatosAlModelo($linea);
    }
}
