<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Esta clase representa los datos parseados de un TXT
 * de novedades de cargo, basado en la documentación SIU.
 * Cada propiedad se extrae según las posiciones fijas
 * y se almacena con una validación inicial.
 */
class NovedadesCargoImportModel extends Model
{
    use HasFactory, MapucheConnectionTrait;

    // -------------------------------------------------------------------------
    // Nombre de la tabla en la base de datos (temporal o definitiva)
    // -------------------------------------------------------------------------
    protected $table = 'suc.novedades_cargo_imports';

    // -------------------------------------------------------------------------
    // Campos que se pueden asignar de forma masiva
    // -------------------------------------------------------------------------
    protected $fillable = [
        'codigoNovedad',
        'numLegajo',
        'numCargo',
        'tipoNovedad',
        'yearVigencia',
        'monthVigencia',
        'numeroLiquidacion',
        'codigoConcepto',
        'novedad1',
        'novedad2',
        'condicionNovedad',
        'yearFinalizacion',
        'monthFinalizacion',
        'yearRetro',
        'monthRetro',
        'yearComienzo',
        'monthComienzo',
        'detalleNovedad',
        'anulaNovedadMultiple',
        'tipoEjercicio',
        'grupoPresupuestario',
        'unidadPrincipal',
        'unidadSubPrincipal',
        'unidadSubSubPrincipal',
        'fuenteFondos',
        'programa',
        'subPrograma',
        'proyecto',
        'actividad',
        'obra',
        'finalidad',
        'funcion',
        'tenerCtaEjercicio',
        'tenerCtaGrupoPresup',
        'tenerCtaUnidadPrincipal',
        'tenerCtaFuente',
        'tenerCtaRedProgramatica',
        'tenerCtaFinalidadFuncion',
        'conActualizacion',
        'nuevosIdentificadores',
        'errors',
    ];

    // -------------------------------------------------------------------------
    // Conversión automática de tipos
    // -------------------------------------------------------------------------
    protected $casts = [
        // Campos numéricos: si se desea preservar ceros a la izquierda,
        // conviene dejarlos como string. Ejemplo: 'numLegajo'
        // se deja sin casting. Para year/month, sí podemos castear a integer.
        'yearVigencia'            => 'integer',
        'monthVigencia'           => 'integer',
        'numeroLiquidacion'       => 'integer',
        'yearFinalizacion'        => 'integer',
        'monthFinalizacion'       => 'integer',
        'yearRetro'               => 'integer',
        'monthRetro'              => 'integer',
        'yearComienzo'            => 'integer',
        'monthComienzo'           => 'integer',
        'grupoPresupuestario'     => 'integer',
        'unidadPrincipal'         => 'integer',
        'unidadSubPrincipal'      => 'integer',
        'unidadSubSubPrincipal'   => 'integer',
        'fuenteFondos'            => 'integer',
        'programa'                => 'integer',
        'subPrograma'             => 'integer',
        'proyecto'                => 'integer',
        'actividad'               => 'integer',
        'obra'                    => 'integer',
        'finalidad'               => 'integer',
        'funcion'                 => 'integer',

        // Banderas booleanas
        'conActualizacion'        => 'boolean',
        'nuevosIdentificadores'   => 'boolean',

        // Lista de errores en formato array
        'errors'                  => 'array',
    ];
}
