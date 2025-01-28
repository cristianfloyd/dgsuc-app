<?php

namespace App\Models;

use App\Services\EncodingService;
use Illuminate\Support\Facades\DB;
use App\Traits\HasCompositePrimaryKey;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Reedware\LaravelCompositeRelations\HasCompositeRelations;

/**
 * Modelo AfipMapucheSicoss
 *
 * Representa los datos de AFIP Mapuche SICOSS en la base de datos.
 */
class AfipMapucheSicoss extends Model
{
    use HasFactory;
    use HasCompositeRelations;
    use MapucheConnectionTrait;




    // Especificar la tabla
    protected $table = 'suc.afip_mapuche_sicoss';

    // Especificar la clave primaria compuesta de la tabla periodo_fiscal y cuil
    //protected $primaryKey = ['periodo_fiscal', 'cuil'];

    protected $primaryKey = ['id'];

    protected $appends = ['id'];

    public $incrementing = false;
    // No necesitas usar timestamps
    public $timestamps = false;

    // Agregar las columnas que pueden ser asignadas masivamente
    protected $fillable = [
        'periodo_fiscal',
        'cuil',
        'apnom',
        'conyuge',
        'cant_hijos',
        'cod_situacion',
        'cod_cond',
        'cod_act',
        'cod_zona',
        'porc_aporte',
        'cod_mod_cont',
        'cod_os',
        'cant_adh',
        'rem_total',
        'rem_impo1',
        'asig_fam_pag',
        'aporte_vol',
        'imp_Adic_os',
        'exc_aport_ss',
        'exc_aport_os',
        'prov',
        'rem_Impo2',
        'rem_Impo3',
        'rem_Impo4',
        'cod_siniestrado',
        'marca_reduccion',
        'recomp_lrt',
        'tipo_empresa',
        'aporte_adic_os',
        'regimen',
        'sit_rev1',
        'dia_ini_sit_rev1',
        'sit_rev2',
        'dia_ini_sit_rev2',
        'sit_rev3',
        'dia_ini_sit_rev3',
        'sueldo_adicc',
        'sac',
        'horas_extras',
        'zona_desfav',
        'vacaciones',
        'cant_dias_trab',
        'rem_impo5',
        'convencionado',
        'rem_impo6',
        'tipo_oper',
        'adicionales',
        'premios',
        'rem_dec_788_05',
        'rem_imp7',
        'nro_horas_ext',
        'cpto_no_remun',
        'maternidad',
        'rectificacion_remun',
        'rem_Imp9',
        'contrib_dif',
        'hstrab',
        'seguro',
        'ley_27430',
        'incsalarial',
        'remimp11',
    ];



    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'conyuge' => 'boolean',
        'cant_hijos' => 'integer',
        'rem_total' => 'decimal:2',
        'rem_impo1' => 'decimal:2',
        'asig_fam_pag' => 'decimal:2',
        'rem_Impo2' => 'decimal:2',
        'rem_Impo3' => 'decimal:2',
        'rem_Impo4' => 'decimal:2',
        'cod_siniestrado' => 'string',
        'marca_reduccion' => 'string',
        'recomp_lrt' => 'decimal:2',
        'tipo_empresa' => 'string',
        'aporte_adic_os' => 'decimal:2',
        'regimen' => 'string',
        'sit_rev1' => 'string',
        'dia_ini_sit_rev1' => 'integer',
        'sit_rev2' => 'string',
        'dia_ini_sit_rev2' => 'integer',
        'sit_rev3' => 'string',
        'dia_ini_sit_rev3' => 'integer',
        'sueldo_adicc' => 'decimal:2',
        'sac' => 'decimal:2',
        'horas_extras' => 'decimal:2',
        'zona_desfav' => 'decimal:2',
        'vacaciones' => 'decimal:2',
        'cant_dias_trab' => 'integer',
        'rem_impo5' => 'decimal:2',
        'convencionado' => 'boolean',
        'rem_impo6' => 'decimal:2',
        'tipo_oper' => 'string',
        'adicionales' => 'decimal:2',
        'premios' => 'decimal:2',
        'rem_dec_788_05' => 'decimal:2',
        'rem_imp7' => 'decimal:2',
        'nro_horas_ext' => 'integer',
        'cpto_no_remun' => 'decimal:2',
        'maternidad' => 'decimal:2',
        'rectificacion_remun' => 'decimal:2',
        'rem_Imp9' => 'decimal:2',
        'contrib_dif' => 'decimal:2',
        'hstrab' => 'decimal:2',
        'seguro' => 'boolean',
        'ley_27430' => 'decimal:2',
        'incsalarial' => 'decimal:2',
        'remimp11' => 'decimal:2',
    ];

    protected $encodedFields = ['apnom', 'prov'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            foreach ($model->encodedFields as $field) {
                $model->setAttribute($field, EncodingService::toLatin1($model->getAttribute($field)));
            }
        });

        static::updating(function ($model) {
            foreach ($model->encodedFields as $field) {
                $model->setAttribute($field, EncodingService::toLatin1($model->getAttribute($field)));
            }
        });
    }

    /**
     * Formatea un valor decimal para el archivo SICOSS
     * Elimina el punto decimal y rellena con ceros a la izquierda
     */
    public static function formatearDecimal($valor, $longitud): string
    {
        return str_pad(
            number_format($valor, 2, '', ''),
            $longitud,
            '0',
            STR_PAD_LEFT
        );
    }

    // ##################################################################################################################
    // Métodos para obtener el ID para FilamentPHP

    public function getKeyName()
    {
        return 'id';
    }

    /**
     * Devuelve la clave compuesta del modelo, que es una combinación del período fiscal y el CUIL.
     * Devolver una representación única de la clave primaria compuesta.
     *
     * @return string La clave compuesta en el formato "periodo_fiscal-cuil".
     */
    public function getKey()
    {
        return "{$this->periodo_fiscal}-{$this->cuil}";
    }

    /**
     * Resuelve el enlace de ruta para este modelo.
     * Este método debe resolver la entidad a partir de la clave compuesta.
     *
     * @param string $value El valor de la clave compuesta (periodo_fiscal-cuil).
     * @param string|null $field El campo opcional a utilizar para la resolución de la ruta.
     * @return AfipMapucheSicoss|null El modelo correspondiente al valor de la clave compuesta, o null si no se encuentra.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        [$periodoFiscal, $cuil] = explode('-', $value);
        return $this->where('periodo_fiscal', $periodoFiscal)
            ->where('cuil', $cuil)
            ->first();
    }

    /**
     * Devuelve el nombre de la key de ruta para este modelo.
     * Este método se utiliza para generar las rutas de Filament PHP.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Agregar un atributo id virtual:
     * Añade un atributo id que combine periodo_fiscal y cuil.
     * @return string
     */
    public function getIdAttribute()
    {
        return "{$this->periodo_fiscal}-{$this->cuil}";
    }

    // Método para obtener el ID virtual para FilamentPHP
    public function getFilamentId(): string
    {
        return $this->id;
    }

    // Método para establecer el ID para FilamentPHP
    public function setFilamentId($value): void
    {
        [$this->periodo_fiscal, $this->cuil] = explode('-', $value);
    }


    // ####################################### RELACIONES ##############################################################

    public function dh01()
    {
        return $this->belongsTo(Dh01::class, 'cuil', 'nro_cuil')
            ->where(function ($query) {
                $query->whereRaw("CONCAT(nro_cuil1, nro_cuil, nro_cuil2) = ?", [$this->cuil]);
            });
    }

    /**
     * Obtiene el registro por período fiscal y CUIL.
     *
     * @param string $periodoFiscal
     * @param string $cuil
     * @return AfipMapucheSicoss|null
     */
    public static function findByPeriodoAndCuil(string $periodoFiscal, string $cuil): ?AfipMapucheSicoss
    {
        return static::where('periodo_fiscal', $periodoFiscal)
            ->where('cuil', $cuil)
            ->first();
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('cuil', 'ilike', '%' . $search . '%')
            ->orWhere('apnom', 'ilike', "%$search%");
    }

    // Agregar un nuevo método para obtener el periodo fiscal formateado si es necesario
    public function getPeriodoFiscalFormateado()
    {
        $periodo = $this->attributes['periodo_fiscal'];
        return substr($periodo, 0, 4) . '-' . substr($periodo, 4, 2);
    }

    // ##################################################################################################################
    // ################################# MUTADORES Y ACCESORES #####################################################

    /**
     * Mutador y Accesor para el campo apnom
     */
    protected function apnom(): Attribute
    {
        return Attribute::make(
            get: fn($value) => EncodingService::toUtf8($value),
            set: fn($value) => EncodingService::toLatin1($value)
        );
    }

    /**
     * Mutador y Accesor para el campo prov
     */
    protected function prov(): Attribute
    {
        return Attribute::make(
            get: fn($value) => EncodingService::toUtf8($value),
            set: fn($value) => EncodingService::toLatin1($value)
        );
    }
}
