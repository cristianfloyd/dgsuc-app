<?php

namespace App\Models;

use App\Models\Mapuche\MapucheGrupo;
use App\Services\EncodingService;
use App\Traits\Mapuche\EncodingTrait;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/**
 * @property int $nro_legaj
 * @property string $desc_appat
 * @property string $desc_apmat
 * @property string $desc_apcas
 * @property string $desc_nombr
 * @property int $nro_tabla
 * @property string $tipo_docum
 * @property string $nro_docum
 * @property string $nro_cuil1
 * @property string $nro_cuil
 * @property string $nro_cuil2
 * @property string $tipo_sexo
 * @property string $fec_nacim
 * @property string $tipo_facto
 * @property string $tipo_rh
 * @property string $nro_ficha
 * @property string $tipo_estad
 * @property string $nombrelugarnac
 * @property string $periodoalta
 * @property string $anioalta
 * @property string $periodoactualizacion
 * @property string $anioactualizacion
 * @property string $pcia_nacim
 * @property string $pais_nacim
 * @property string $cuil
 * @property string $cuil_completo
 */
class Dh01 extends Model
{
    use MapucheConnectionTrait;
    use EncodingTrait;

    public $timestamps = false;

    protected $table = 'dh01';

    protected $primaryKey = 'nro_legaj';

    /**
     * Campos que requieren conversión de codificación.
     *
     * @var array<string>
     */
    protected $encodedFields = [
        'desc_appat',
        'desc_apmat',
        'desc_apcas',
        'desc_nombr',
    ];

    /**
     * Campos que se pueden llenar en masa.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nro_legaj',
        'desc_appat',
        'desc_apmat',
        'desc_apcas',
        'desc_nombr',
        'nro_tabla',
        'tipo_docum',
        'nro_docum',
        'nro_cuil1',
        'nro_cuil',
        'nro_cuil2',
        'tipo_sexo',
        'fec_nacim',
        'tipo_facto',
        'tipo_rh',
        'nro_ficha',
        'tipo_estad',
        'nombrelugarnac',
        'periodoalta',
        'anioalta',
        'periodoactualizacion',
        'anioactualizacion',
        'pcia_nacim',
        'pais_nacim',
    ];

    protected $appends = [
        'cuil',
        'cuil_completo',
    ];

    // ###################################################################################
    // ######################################  RELACIONES ################################
    public function relacionesActivas(): BelongsTo
    {
        return $this->belongsTo(AfipRelacionesActivas::class, 'cuil', 'cuil');
    }

    public function dh03()
    {
        return $this->hasMany(Dh03::class, 'nro_legaj', 'nro_legaj');
    }

    public function cargos()
    {
        return $this->hasMany(Dh03::class, 'nro_legaj', 'nro_legaj');
    }

    public function dh21()
    {
        return $this->hasMany(Dh21::class, 'nro_legaj', 'nro_legaj');
    }

    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(
            MapucheGrupo::class,
            'mapuche.grupo_x_legajo',
            'nro_legaj',
            'id_grupo',
        );
    }

    // ####################################################################################
    // ######################################  SCOPES  ####################################

    public function scopeSearch($query, $val)
    {
        $searchTerm = EncodingService::toLatin1(strtoupper((string) $val));

        return $query->where('nro_legaj', 'like', "%$val%")
            ->orWhere('nro_cuil', 'like', "%$val%")
            ->orWhere('desc_appat', 'like', '%' . strtoupper((string) $searchTerm) . '%')
            ->orWhere('desc_apmat', 'like', '%' . strtoupper((string) $searchTerm) . '%')
            ->orWhere('desc_apcas', 'like', '%' . strtoupper((string) $searchTerm) . '%')
            ->orWhere('desc_nombr', 'like', '%' . strtoupper((string) $searchTerm) . '%');
    }

    /**
     * Scope para obtener legajos activos sin cargos vigentes en el periodo actual.
     *
     * Este scope consulta la base de datos para encontrar legajos que:
     * - Tienen estado 'A' (Activo)
     * - No tienen cargos activos en dh03
     * - Cumplen con la condición adicional especificada en $where
     *
     *  Query builder con los siguientes campos:
     *                                               - nro_legaj: Número de legajo
     *                                               - nro_docum: Número de documento formateado (tipo + número con separadores)
     *                                               - cuil: CUIL formateado con guiones (XX-XXXXXXXX-X)
     *                                               - tipo_estad: Estado del legajo
     *                                               - agente: Nombre completo del agente (apellido, nombre)
     */
    public function scopeLegajosActivosSinCargosVigentes($query, $where = '1=1')
    {
        return $query->select([
            'nro_legaj',
            DB::raw("tipo_docum || ' ' || to_char(nro_docum::numeric(11,0),'9G999G999G999') AS nro_docum"),
            DB::raw("LPAD(nro_cuil1::varchar, 2, '0') || '-' || LPAD(nro_cuil::varchar, 8, '0') || '-' || nro_cuil2 AS cuil"),
            'tipo_estad',
            DB::raw("desc_appat ||', '|| desc_nombr as agente"),
        ])
            ->where('tipo_estad', 'A')
            ->whereNotExists(function ($subquery): void {
                $subquery->select(DB::raw(1))
                    ->from('mapuche.dh03 as car')
                    ->whereRaw('car.nro_legaj = dh01.nro_legaj')
                    ->whereRaw('mapuche.map_es_cargo_activo(car.nro_cargo)');
            })
            ->whereRaw($where)
            ->orderBy('nro_legaj');
    }

    public function scopeByCuil($query, $cuil)
    {
        return $query->whereRaw("(
            LPAD(nro_cuil1::text, 2, '0') ||
            LPAD(nro_cuil::text, 8, '0') ||
            LPAD(nro_cuil2::text, 1, '0')
        ) = ?", [$cuil]);
    }

    protected function cuilCompleto(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function (): string {
            // Aseguramos que cada parte tenga el largo correcto
            $cuil1 = str_pad($this->nro_cuil1, 2, '0', \STR_PAD_LEFT);
            $cuil = str_pad($this->nro_cuil, 8, '0', \STR_PAD_LEFT);
            $cuil2 = str_pad($this->nro_cuil2, 1, '0', \STR_PAD_LEFT);
            return $cuil1 . $cuil . $cuil2;
        });
    }

    /**
     * Verifica si un legajo específico está jubilado.
     *
     * @param string $nro_legajo Número de legajo a verificar.
     *
     * @return bool Retorna verdadero si el legajo está jubilado, falso de lo contrario.
     */
    public static function esJubilado($nro_legajo): bool
    {
        return static::query()
            ->where('nro_legaj', $nro_legajo)
            ->where('tipo_estad', 'J')
            ->exists();
    }
    protected function descNombr(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: fn($value): ?string => EncodingService::toUtf8(trim((string) $value)), set: fn($value): array => ['desc_nombr' => EncodingService::toLatin1($value)]);
    }

    public function getCuil(): Attribute
    {
        return Attribute::make(
            get: fn(): string => "{$this->nro_cuil1}{$this->nro_cuil}{$this->nro_cuil2}",
        );
    }

    protected function descAppat(): Attribute
    {
        return Attribute::make(get: fn($value): ?string => EncodingService::toUtf8(trim((string) $value)));
    }

    public function NombreCompleto(): Attribute
    {
        return Attribute::make(
            get: fn(): string => "{$this->desc_appat}, {$this->desc_nombr}",
        );
    }

    // ####################################################################################
    // ######################################  FUNCIONES  ####################################

    /**
     * Obtiene legajos activos sin cargos vigentes como array.
     *
     * @param string $where Condición adicional WHERE
     *
     * @return array
     */
    public static function getLegajosActivosSinCargosVigentes($where)
    {
        return self::legajosActivosSinCargosVigentes($where)->get()->toArray();
    }

    /**
     * Obtiene un legajo activo sin cargos vigentes y sin registros en dh21.
     *
     * @param int $nro_legajo Número de legajo a buscar
     *
     * @return array|null Retorna el legajo si cumple las condiciones o null si no existe
     */
    public static function getLegajoSinLiquidarYSinDh21(int $nro_legajo): ?array
    {
        $where_not_dh21 = "
        NOT EXISTS (SELECT 1
                    FROM mapuche.dh21
                    WHERE dh21.nro_legaj = dh01.nro_legaj)
        AND dh01.nro_legaj = $nro_legajo
    ";

        $resultado = static::legajosActivosSinCargosVigentes($where_not_dh21)->first();

        return $resultado ? $resultado->toArray() : null;
    }

    protected function cuil(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                // Aseguramos que cada parte tenga el largo correcto
                $cuil1 = str_pad($this->nro_cuil1, 2, '0', \STR_PAD_LEFT);
                $cuil = str_pad($this->nro_cuil, 8, '0', \STR_PAD_LEFT);
                $cuil2 = str_pad($this->nro_cuil2, 1, '0', \STR_PAD_LEFT);

                return "$cuil1$cuil$cuil2";
            },
        );
    }
}
