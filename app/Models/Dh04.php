<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent para la tabla mapuche.dh04.
 *
 * Esta clase representa las otras actividades de los empleados en el sistema Mapuche.
 */
class Dh04 extends Model
{
    use MapucheConnectionTrait;

    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Nombre de la tabla asociada al modelo.
     *
     * @var string
     */
    protected $table = 'dh04';

    /**
     * La clave primaria asociada con la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'nro_otra_actividad';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'nro_legaj', 'tipo_activ', 'desc_entid', 'nro_cuit', 'desc_cargo',
        'cant_hs', 'fec_ingre', 'fec_egres', 'codc_dedic', 'vig_otano',
        'vig_otmes', 'dominstitucion', 'relprofesion', 'aporta_antig_remun',
        'aporta_antig_lao', 'aporta_ant_jubil', 'mes_vigencia', 'anio_vigencia',
        'codmotivobaja', 'codescalafonoa', 'codcategoriaoa', 'codsistemaacceso', 'codgradooa',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'nro_cuit' => 'decimal:0',
        'fec_ingre' => 'date',
        'fec_egres' => 'date',
        'relprofesion' => 'boolean',
        'aporta_antig_remun' => 'boolean',
        'aporta_antig_lao' => 'boolean',
        'aporta_ant_jubil' => 'boolean',
    ];

    /**
     * Obtiene el legajo asociado a esta actividad.
     */
    public function legajo()
    {
        return $this->belongsTo(Dh01::class, 'nro_legaj', 'nro_legaj');
    }

    /**
     * Obtiene el motivo de baja asociado a esta actividad.
     */
    public function motivoBaja()
    {
        return $this->belongsTo(Dhb3::class, 'codmotivobaja', 'codigo');
    }

    /**
     * Obtiene el escalafón asociado a esta actividad.
     */
    public function escalafon()
    {
        return $this->belongsTo(Dhe5::class, 'codescalafonoa', 'codigoescalafonoa');
    }

    /**
     * Obtiene la categoría asociada a esta actividad.
     */
    public function categoria()
    {
        return $this->belongsTo(Dhe6::class, 'codcategoriaoa', 'codigocategoriaoa');
    }

    /**
     * Obtiene el sistema de acceso asociado a esta actividad.
     */
    public function sistemaAcceso()
    {
        return $this->belongsTo(Dhe7::class, 'codsistemaacceso', 'codigoaccesoescalafon');
    }

    /**
     * Obtiene el grado asociado a esta actividad.
     */
    public function grado()
    {
        return $this->belongsTo(Dhe8::class, 'codgradooa', 'codigogradooa');
    }

    public function scopeActivo($query)
    {
        return $query->whereNull('fec_egres');
    }
}
