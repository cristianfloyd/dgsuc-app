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

class Dh01 extends Model
{
    use MapucheConnectionTrait;
    use EncodingTrait;

    public $timestamps = false;

    protected $table = 'dh01';

    protected $primaryKey = 'nro_legaj';

    /**
     * Campos que requieren conversión de codificación.
     */
    protected $encodedFields = [
        'desc_appat',
        'desc_apmat',
        'desc_apcas',
        'desc_nombr',
    ];

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
        return $this->hasMany(dh03::class, 'nro_legaj', 'nro_legaj');
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
        $searchTerm = EncodingService::toLatin1(strtoupper($val));

        return $query->where('nro_legaj', 'like', "%$val%")
            ->orWhere('nro_cuil', 'like', "%$val%")
            ->orWhere('desc_appat', 'like', '%' . strtoupper($searchTerm) . '%')
            ->orWhere('desc_apmat', 'like', '%' . strtoupper($searchTerm) . '%')
            ->orWhere('desc_apcas', 'like', '%' . strtoupper($searchTerm) . '%')
            ->orWhere('desc_nombr', 'like', '%' . strtoupper($searchTerm) . '%');
    }

    /**
     * Scope para obtener legajos activos sin cargos vigentes en el periodo actual.
     *
     * Este scope consulta la base de datos para encontrar legajos que:
     * - Tienen estado 'A' (Activo)
     * - No tienen cargos activos en dh03
     * - Cumplen con la condición adicional especificada en $where
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Query builder instance
     * @param string $where Condición SQL adicional para filtrar los resultados (default: '1=1')
     *
     * @return \Illuminate\Database\Eloquent\Builder Query builder con los siguientes campos:
     *                                               - nro_legaj: Número de legajo
     *                                               - nro_docum: Número de documento formateado (tipo + número con separadores)
     *                                               - cuil: CUIL formateado con guiones (XX-XXXXXXXX-X)
     *                                               - tipo_estad: Estado del legajo
     *                                               - agente: Nombre completo del agente (apellido, nombre)
     */
    public function scopeLegajosActivosSinCargosVigentes($query, $where = '1=1')
    {
        $query = $query->select([
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
        return $query;
    }

    public function scopeByCuil($query, $cuil)
    {
        return $query->whereRaw("(
            LPAD(nro_cuil1::text, 2, '0') ||
            LPAD(nro_cuil::text, 8, '0') ||
            LPAD(nro_cuil2::text, 1, '0')
        ) = ?", [$cuil]);
    }

    public function getCuilCompletoAttribute()
    {
        // Aseguramos que cada parte tenga el largo correcto
        $cuil1 = str_pad($this->nro_cuil1, 2, '0', \STR_PAD_LEFT);
        $cuil = str_pad($this->nro_cuil, 8, '0', \STR_PAD_LEFT);
        $cuil2 = str_pad($this->nro_cuil2, 1, '0', \STR_PAD_LEFT);

        return $cuil1 . $cuil . $cuil2;
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

    // ###############################################
    // ###########  Mutadores y Accesores  ###########

    public function getDescNombrAttribute($value)
    {
        return EncodingService::toUtf8(trim($value));
    }

    public function setDescNombrAttribute($value): void
    {
        $this->attributes['desc_nombr'] = EncodingService::toLatin1($value);
    }

    public function getCuil(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->nro_cuil1}{$this->nro_cuil}{$this->nro_cuil2}",
        );
    }

    public function getDescAppatAttribute($value)
    {
        return EncodingService::toUtf8(trim($value));
    }

    public function NombreCompleto(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->desc_appat}, {$this->desc_nombr}",
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
            get: function () {
                // Aseguramos que cada parte tenga el largo correcto
                $cuil1 = str_pad($this->nro_cuil1, 2, '0', \STR_PAD_LEFT);
                $cuil = str_pad($this->nro_cuil, 8, '0', \STR_PAD_LEFT);
                $cuil2 = str_pad($this->nro_cuil2, 1, '0', \STR_PAD_LEFT);

                return "$cuil1$cuil$cuil2";
            },
        );
    }
}
