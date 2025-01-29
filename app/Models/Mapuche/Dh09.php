<?php

declare(strict_types=1);

namespace App\Models\Mapuche;

use Carbon\Carbon;
use App\Data\Mapuche\Dh09Data;
use App\Traits\Mapuche\Dh09Queries;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo para la tabla de datos personales DH09
 *
 * @property int $nro_legaj Número de Legajo (Primary Key)
 * @property int|null $vig_otano Año de vigencia
 * @property int|null $vig_otmes Mes de vigencia
 * @property int|null $nro_tab02 Número de tabla 02
 * @property string|null $codc_estcv Código estado civil
 * @property bool $sino_embargo Indicador de embargo
 * @property string|null $sino_otsal Indicador otros salarios
 * @property string|null $sino_jubil Indicador jubilación
 * @property Carbon|null $fec_altos Fecha de alta
 * @property Carbon|null $fec_defun Fecha de defunción
 * [... resto de propiedades ...]
 */
class Dh09 extends Model
{
    use HasFactory, MapucheConnectionTrait;
    use Dh09Queries;

    protected $table = 'mapuche.dh09';
    protected $primaryKey = 'nro_legaj';
    public $timestamps = false;

    protected $fillable = [
        'vig_otano',
        'vig_otmes',
        'nro_tab02',
        'codc_estcv',
        'sino_embargo',
        'sino_otsal',
        'sino_jubil',
        'nro_tab08',
        'codc_bprev',
        'nro_tab09',
        'codc_obsoc',
        'nro_afili',
        'fec_altos',
        'fec_endjp',
        'desc_envio',
        'cant_cargo',
        'desc_tarea',
        'codc_regio',
        'codc_uacad',
        'fec_vtosf',
        'fec_reasf',
        'fec_defun',
        'fecha_jubilacion',
        'fecha_grado',
        'nro_agremiacion',
        'fecha_permanencia',
        'ua_asigfamiliar',
        'fechadjur894',
        'renunciadj894',
        'fechadechere',
        'coddependesemp',
        'conyugedependiente',
        'fec_ingreso',
        'codc_uacad_seguro',
        'fecha_recibo',
        'tipo_norma',
        'nro_norma',
        'tipo_emite',
        'fec_norma',
        'fuerza_reparto'
    ];

    protected $casts = [
        'nro_legaj' => 'integer',
        'vig_otano' => 'integer',
        'vig_otmes' => 'integer',
        'nro_tab02' => 'integer',
        'codc_estcv' => 'string',
        'sino_embargo' => 'boolean',
        'sino_otsal' => 'string',
        'sino_jubil' => 'string',
        'fec_altos' => 'date',
        'fec_endjp' => 'date',
        'cant_cargo' => 'integer',
        'fec_vtosf' => 'date',
        'fec_reasf' => 'date',
        'fec_defun' => 'date',
        'fecha_jubilacion' => 'date',
        'fecha_grado' => 'date',
        'nro_agremiacion' => 'integer',
        'fecha_permanencia' => 'date',
        'fechadjur894' => 'date',
        'fechadechere' => 'date',
        'conyugedependiente' => 'integer',
        'fec_ingreso' => 'date',
        'fecha_recibo' => 'date',
        'nro_norma' => 'integer',
        'fec_norma' => 'date',
        'fuerza_reparto' => 'boolean'
    ];
}
