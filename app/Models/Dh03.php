<?php

namespace App\Models;

use App\Models\SpuDisc;
use App\Enums\LegajoCargo;
use App\Models\Mapuche\Dh05;
use Illuminate\Support\Facades\DB;
use App\Models\Mapuche\Catalogo\Dh30;
use App\Models\Mapuche\Catalogo\Dh36;
use App\Models\Mapuche\MapucheConfig;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dh03 extends Model
{
    use MapucheConnectionTrait;


    protected $table = 'dh03';
    public $timestamps = false;
    protected $primaryKey = 'nro_cargo';

    protected $fillable = [
        'nro_cargo',
        'rama',
        'disciplina',
        'area',
        'porcdedicdocente',
        'porcdedicinvestig',
        'porcdedicagestion',
        'porcdedicaextens',
        'codigocontrato',
        'horassemanales',
        'duracioncontrato',
        'incisoimputacion',
        'montocontrato',
        'nro_legaj',
        'fec_alta',
        'fec_baja',
        'codc_carac',
        'codc_categ',
        'codc_agrup',
        'tipo_norma',
        'tipo_emite',
        'fec_norma',
        'nro_norma',
        'codc_secex',
        'nro_exped',
        'nro_exped_baja',
        'fec_exped_baja',
        'ano_exped_baja',
        'codc_secex_baja',
        'ano_exped',
        'fec_exped',
        'nro_tab13',
        'codc_uacad',
        'nro_tab18',
        'codc_regio',
        'codc_grado',
        'vig_caano',
        'vig_cames',
        'chk_proye',
        'tipo_incen',
        'dedi_incen',
        'cic_con',
        'fec_limite',
        'porc_aplic',
        'hs_dedic',
        'tipo_norma_baja',
        'tipoemitenormabaja',
        'fecha_norma_baja',
        'fechanotificacion',
        'coddependesemp',
        'chkfirmaencargado',
        'chkfirmaautoridad',
        'chkestadoafip',
        'chkestadotitulo',
        'chkestadocv',
        'objetocontrato',
        'nro_norma_baja',
        'fechagrado',
        'fechapermanencia',
        'fecaltadesig',
        'fecbajadesig',
        'motivoaltadesig',
        'motivobajadesig',
        'renovacion',
        'idtareacargo',
        'chktrayectoria',
        'chkfuncionejec',
        'chkretroactivo',
        'chkstopliq',
        'nro_cargo_ant',
        'transito',
        'cod_clasif_cargo',
        'cod_licencia',
        'cargo_concursado'
    ];

    protected $casts = [
        'fec_alta' => 'date:Y-m-d',
        'fec_baja' => 'date:Y-m-d',
        'fec_norma' => 'date:Y-m-d',
        'fec_exped' => 'date:Y-m-d',
        'fec_exped_baja' => 'date:Y-m-d',
        'fec_limite' => 'date:Y-m-d',
        'fecha_norma_baja' => 'date:Y-m-d',
        'fechanotificacion' => 'date:Y-m-d',
        'fechagrado' => 'date:Y-m-d',
        'fechapermanencia' => 'date:Y-m-d',
        'fecaltadesig' => 'date:Y-m-d',
        'fecbajadesig' => 'date:Y-m-d',
        'chk_proye' => 'boolean',
        'chktrayectoria' => 'boolean',
        'chkfuncionejec' => 'boolean',
        'chkretroactivo' => 'boolean',
        'cargo_concursado' => 'boolean',
        'chkstopliq' => 'integer',
        'nro_cargo' => 'integer',
        'nro_legaj' => 'integer',
        'porcdedicdocente' => 'double',
        'porcdedicinvestig' => 'double',
        'porcdedicagestion' => 'double',
        'porcdedicaextens' => 'double'
    ];

    protected $attributes = [
        'chkstopliq' => 0,
        'porcdedicdocente' => 0,
        'porcdedicinvestig' => 0,
        'porcdedicagestion' => 0,
        'porcdedicaextens' => 0,
        'chk_proye' => true,
        'chktrayectoria' => true,
        'chkfuncionejec' => false,
        'chkretroactivo' => false,
        'cargo_concursado' => false
    ];


    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Validar porcentajes
            if (
                $model->porcdedicdocente + $model->porcdedicinvestig +
                $model->porcdedicagestion + $model->porcdedicaextens > 100
            ) {
                throw new \Exception('La suma de porcentajes de dedicación no puede superar 100%');
            }

            // Validar fechas coherentes
            if (
                $model->fec_baja && $model->fec_alta &&
                $model->fec_baja < $model->fec_alta
            ) {
                throw new \Exception('La fecha de baja no puede ser anterior a la fecha de alta');
            }
        });
    }

    protected function setCodigocontratoAttribute($value)
    {
        $this->attributes['codigocontrato'] = (int)$value;
    }

    protected function setChkstopliqAttribute($value)
    {
        $this->attributes['chkstopliq'] = (int)$value;
    }

    /* ############################### GETTERS ############################## */
    public static function getCargoCount(): int
    {
        return Dh03::count();
    }

    /**
     * Obtiene los detalles completos de la validación
     *
     * @param int $nroLegaj
     * @param int $nroCargo
     * @return array
     */
    public static function getDetallesValidacion(int $nroLegaj, int $nroCargo): array
    {
        $cargo = static::validarLegajoCargo($nroLegaj, $nroCargo)->first();

        return [
            'existe' => (bool)$cargo,
            'detalles' => $cargo ? [
                'legajo' => $cargo->nro_legaj,
                'cargo' => $cargo->nro_cargo,
                'estado' => $cargo->chkstopliq ? 'Bloqueado' : 'Activo',
                'fecha_baja' => $cargo->fec_baja,
            ] : null
        ];
    }


    /**
     * Obtiene los cargos activos de un legajo
     *
     * @param int $nroLegajo
     * @return array
     */
    public static function getCargosActivos(int $nroLegajo): array
    {
        return static::cargosActivos($nroLegajo)->get()->toArray();
    }



    /** ############################## SCOPES ############################## */

    public function scopeEmpleadosActivos($query)
    {
        return $query->where('chkstopliq', '=', 0)
            ->where('codc_uacad', '!=', '')
            ->whereNull('fec_baja');
    }

    /**
     * Scope para validar la combinación legajo-cargo
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $nroLegaj
     * @param int $nroCargo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValidarLegajoCargo($query, int $nro_legaj, int $nro_cargo)
    {
        return $query->where('nro_legaj', $nro_legaj)
            ->where('nro_cargo', $nro_cargo);
    }

    /**
     * Scope para obtener cargos activos de un legajo
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $nroLegajo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCargosActivos($query, int $nroLegajo)
    {
        $fecha = MapucheConfig::getFechaFinPeriodoCorriente();

        return $query->select([
            'nro_cargo',
            DB::raw("CASE
                WHEN (codc_categ = '' OR codc_categ IS NULL)
                THEN nro_cargo::TEXT
                ELSE codc_categ
            END AS codc_categ")
        ])
            ->where(function ($query) use ($fecha) {
                $query->whereNull('fec_baja')
                    ->orWhere('fec_baja', '>=', $fecha);
            })
            ->where('nro_legaj', $nroLegajo);
    }



    /* ############################## RELACIONES ############################## */

    public function dh21()
    {
        return $this->hasMany(Dh21::class, 'nro_cargo', 'nro_cargo');
    }
    public function dh01(): BelongsTo
    {
        return $this->belongsTo(Dh01::class, 'nro_legaj', 'nro_legaj');
    }

    public function dh11(): BelongsTo
    {
        return $this->belongsTo(Dh11::class, 'codc_categ', 'codc_categ');
    }

    public function dhc9(): BelongsTo
    {
        return $this->belongsTo(Dhc9::class, 'codc_agrup', 'codagrup');
    }


    public function dhd7(): BelongsTo
    {
        return $this->belongsTo(Dhd7::class, 'cod_clasif_cargo', 'cod_clasif_cargo');
    }


    public function spuDisc(): BelongsTo
    {
        return $this->belongsTo(SpuDisc::class, 'rama', 'rama')
            ->where('disciplina', $this->disciplina)
            ->where('area', $this->area);
    }

    public function dh05(): BelongsTo
    {
        return $this->belongsTo(Dh05::class, 'nro_licencia', 'nro_licencia');
    }

    /**
     * Obtiene la unidad académica asociada al cargo.
     *
     * @return BelongsTo
     */
    public function dh30(): BelongsTo
    {
        return $this->belongsTo(Dh30::class, 'codc_uacad', 'desc_abrev')
            ->where('nro_tabla', 13);
    }

    /**
     * Obtiene la dependencia de desempeño asociada al cargo.
     *
     * @return BelongsTo
     */
    public function dh36(): BelongsTo
    {
        return $this->belongsTo(Dh36::class, 'coddependesemp', 'coddependesemp');
    }

    // ######################## MUTADORES ##############################
    public function getCodcUacadAttribute($value)
    {
        return trim($value);
    }

    /* ##################### ATRIBUTOS ##################### */
    protected function legajoCargo(): Attribute
    {
        return Attribute::make(
            get: fn() => LegajoCargo::from($this->nro_legaj, $this->nro_cargo),
        );
    }

    /*  ##################### HELPER ##################### */
    public static function validarParLegajoCargo(int $nroLegaj, int $nroCargo): bool
    {
        return static::validarLegajoCargo($nroLegaj, $nroCargo)->exists();
    }

    /**
     * Método estático para buscar un cargo por legajo y número de cargo
     *
     * @param int $nroLegaj
     * @param int $nroCargo
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function buscarPorLegajoCargo(int $nroLegaj, int $nroCargo)
    {
        return static::query()->where('nro_legaj', $nroLegaj)
            ->where('nro_cargo', $nroCargo);
    }
}
