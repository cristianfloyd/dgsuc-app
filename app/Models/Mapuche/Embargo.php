<?php

declare(strict_types=1);

namespace App\Models\Mapuche;

use Carbon\Carbon;
use App\Models\Dh01;
use App\Models\Dh03;
use App\Models\Dh21;
use App\Traits\EmbargoQueries;
use App\Services\EncodingService;
use App\Traits\Mapuche\EncodingTrait;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Mapuche\Embargos\Juzgado;
use App\Models\Mapuche\Embargos\TipoJuicio;
use App\Models\Mapuche\Embargos\TipoEmbargo;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Mapuche\Embargos\Beneficiario;
use App\Models\Mapuche\Embargos\EstadoEmbargo;
use App\Models\Mapuche\Embargos\CuentaJudicial;
use App\Models\Mapuche\Embargos\TipoExpediente;
use App\Models\Mapuche\Embargos\TipoRemuneracion;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modelo Eloquent para la tabla mapuche.emb_embargo
 *
 * @property int $nro_embargo Número de embargo (PK)
 * @property int $nro_legaj Número de legajo
 * @property int|null $es_de_legajo Legajo relacionado
 * @property int $id_tipo_remuneracion Tipo de remuneración
 * @property int $id_tipo_embargo Tipo de embargo
 * @property int $id_estado_embargo Estado del embargo
 * @property int|null $nro_oficio Número de oficio
 * @property int $id_juzgado Juzgado
 * @property string $cuit CUIT del beneficiario
 * @property string|null $lugar_pago Lugar de pago
 * @property string|null $nro_expediente_original Número expediente original
 * @property string|null $nro_expediente_ampliatorio Número expediente ampliatorio
 * @property string $nro_expediente_institucion Número expediente institución
 * @property string|null $caratula Carátula del expediente
 * @property Carbon|null $fec_inicio Fecha de inicio
 * @property Carbon|null $fec_finalizacion Fecha de finalización
 * @property float|null $imp_embargo Importe del embargo
 * @property Carbon|null $fec_ingreso_expediente Fecha ingreso expediente
 * @property string|null $obs_embargo Observaciones
 * @property int|null $prioridad Prioridad
 * @property string|null $nro_cuenta_judicial Número cuenta judicial
 * @property int|null $codigo_sucursal Código sucursal
 * @property int|null $nroentidadbancaria Número entidad bancaria
 * @property int $codn_conce Código concepto
 * @property float|null $cuota_embargo Cuota embargo
 * @property int|null $id_tipo_juicio Tipo de juicio
 * @property string|null $nom_demandado Nombre demandado
 * @property Carbon|null $fec_oficio Fecha oficio
 * @property int|null $id_tipo_expediente Tipo expediente
 */
class Embargo extends Model
{
    use HasFactory;
    use EmbargoQueries;
    use MapucheConnectionTrait;
    use EncodingTrait;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'mapuche.emb_embargo';

    /**
     * Clave primaria
     */
    protected $primaryKey = 'nro_embargo';

    /**
     * Desactivar timestamps de Laravel
     */
    public $timestamps = false;

    protected $encodedFields = [
        'lugar_pago',
        'caratula',
        'obs_embargo',
        'nom_demandado',
    ];

    /**
     * Atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'nro_legaj',
        'es_de_legajo',
        'id_tipo_remuneracion',
        'id_tipo_embargo',
        'id_estado_embargo',
        'nro_oficio',
        'id_juzgado',
        'cuit',
        'lugar_pago',
        'nro_expediente_original',
        'nro_expediente_ampliatorio',
        'nro_expediente_institucion',
        'caratula',
        'fec_inicio',
        'fec_finalizacion',
        'imp_embargo',
        'fec_ingreso_expediente',
        'obs_embargo',
        'prioridad',
        'nro_cuenta_judicial',
        'codigo_sucursal',
        'nroentidadbancaria',
        'codn_conce',
        'cuota_embargo',
        'id_tipo_juicio',
        'nom_demandado',
        'fec_oficio',
        'id_tipo_expediente'
    ];

    /**
     * Casting de atributos
     */
    protected $casts = [
        'fec_inicio' => 'date',
        'fec_finalizacion' => 'date',
        'fec_ingreso_expediente' => 'date',
        'fec_oficio' => 'date',
        'imp_embargo' => 'float',
        'cuota_embargo' => 'float',
        'cuit' => 'string',
        'nro_expediente_original' => 'string',
        'nro_expediente_ampliatorio' => 'string',
        'nro_cuenta_judicial' => 'string'
    ];


    public function detallenovedad(): Attribute
    {
        return new Attribute(
            get: function () {
                return "{$this->codn_conce}-{$this->nro_oficio}";
            }
        );
    }

    public function getImporteDescontado(int $nro_liqui): Collection
    {
        $importe = Dh21::query()
            ->where('nro_liqui', $nro_liqui)
            ->where('nro_legaj', $this->nro_legaj)
            ->where('codn_conce', $this->codn_conce)
            ->where('detallenovedad', $this->detallenovedad)
            ->where('impp_conce', '>', 0)
            ->orderBy('nro_legaj', 'desc')
            ->get(['nro_cargo', 'impp_conce']);


        return  $importe;
    }

    // ############### ACCESORES Y MUTADORES ####################
    public function getCaratulaAttribute($value)
    {
        return EncodingService::toUtf8($value);
    }

    public function getNomDemandadoAttribute()
    {
        return EncodingService::toUtf8($this->nom_demandado);
    }

    // ############### RELACIONES ####################
    /**
     * Relación con el beneficiario
     */
    public function beneficiario(): BelongsTo
    {
        return $this->belongsTo(Beneficiario::class, 'cuit', 'cuit');
    }


    /**
     * Relación con el estado del embargo
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoEmbargo::class, 'id_estado_embargo');
    }

    /**
     * Relación con el tipo de remuneración
     */
    public function tipoRemuneracion(): BelongsTo
    {
        return $this->belongsTo(TipoRemuneracion::class, 'id_tipo_remuneracion');
    }

    /**
     * Relación con el tipo de embargo
     */
    public function tipoEmbargo(): BelongsTo
    {
        return $this->belongsTo(TipoEmbargo::class, 'id_tipo_embargo');
    }

    /**
     * Relación con el juzgado
     */
    public function juzgado(): BelongsTo
    {
        return $this->belongsTo(Juzgado::class, 'id_juzgado');
    }

    /**
     * Relación con la cuenta judicial
     */
    public function cuentaJudicial(): BelongsTo
    {
        // return $this->belongsTo(CuentaJudicial::class, ['nro_cuenta_judicial', 'codigo_sucursal', 'nroentidadbancaria']);
        return $this->belongsTo(CuentaJudicial::class, 'nro_cuenta_judicial', 'nro_cuenta_judicial')
            ->where('codigo_sucursal', $this->codigo_sucursal)
            ->where('nroentidadbancaria', $this->nroentidadbancaria);
    }

    /**
     * Relación con el tipo de juicio
     */
    public function tipoJuicio(): BelongsTo
    {
        return $this->belongsTo(TipoJuicio::class, 'id_tipo_juicio');
    }

    /**
     * Relación con el tipo de expediente
     */
    public function tipoExpediente(): BelongsTo
    {
        return $this->belongsTo(TipoExpediente::class, 'id_tipo_expediente');
    }

    /*
     *   Relacion con datos personales
    */
    public function datosPersonales(): BelongsTo
    {
        return $this->belongsTo(Dh01::class, 'nro_legaj', 'nro_legaj');
    }

    /**
     * Obtiene la relación de liquidaciones asociadas al embargo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function liquidaciones(): HasMany
    {
        return $this->hasMany(Dh21::class, 'nro_legaj', 'nro_legaj');
    }

    /**
     * Obtiene la relación de cargos asociados al embargo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cargo(): HasMany
    {
        return $this->hasMany(Dh03::class, 'nro_legaj', 'nro_legaj');
    }

    /**
     * Definir los campos de búsqueda globales
     */
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nro_embargo',
            'nro_legaj',
            'datosPersonales.desc_appat',
            'datosPersonales.desc_apmat',
            'datosPersonales.desc_apcas',
            'datosPersonales.desc_nombr',
            'datosPersonales.nombre_completo',
        ];
    }
}
