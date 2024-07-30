<?php

namespace App\Providers;

use App\services\ColumnMetadata;
use Illuminate\Support\ServiceProvider;

class ColumnMetadataServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ColumnMetadata::class, function($app){
            $columnWidths = [
                6,  //periodo fiscal
                2,  //codigo movimiento
                2,  //Tipo de registro
                11,  //CUIL del empleado
                1,  //Marca de trabajador agropecuario
                3,  //Modalidad de contrato
                10,  //Fecha de inicio de la rel. Laboral
                10,  //Fecha de fin relacion laboral
                6,  //Código de obra social
                2,  //codigo situacion baja
                10,  //Fecha telegrama renuncia
                15,  //Retribución pactada
                1,  //Modalidad de liquidación
                5,  //Sucursal-Domicilio de desempeño
                6,  //Actividad en el domicilio de desempeño
                4,  //Puesto desempeñado
                1,  //Rectificación
                10,  //Numero Formulario Agropecuario
                3,  //Tipo de Servicio
                6,  //Categoría Profesional
                7,  //Código de Convenio Colectivo de Trabajo
                4,  //Sin valores, en blanco
            ];
            return new ColumnMetadata($columnWidths);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
