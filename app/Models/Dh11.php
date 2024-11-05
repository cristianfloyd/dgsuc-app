<?php

namespace App\Models;

use App\Traits\PostgresqlTrait;
use App\Traits\HasLaboralStatus;
use Illuminate\Support\Facades\Cache;
use App\Traits\MapucheConnectionTrait;
use App\Traits\CategoriasConstantTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\CategoryUpdateServiceInterface;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo para la tabla de Categorías del sistema Mapuche
 *
 * @property string $codc_categ Código de categoría (PK)
 * @property string|null $equivalencia Equivalencia Sipuver
 * @property string|null $tipo_escal Tipo de escalafón
 * @property int|null $nro_escal Número de escalafón
 * @property float|null $impp_basic Importe básico
 * @property string|null $codc_dedic Código de dedicación (FK)
 * ...
 * @method static whereIn(string $string, string[] $DOCS)
 * @method static updateOrCreate(array $attributes, array $values)
 */
class Dh11 extends Model
{
    use MapucheConnectionTrait, CategoriasConstantTrait, PostgresqlTrait, HasLaboralStatus;

    public $timestamps = false;
    public $incrementing = false;
    /**
     * Nombre de la tabla de base de datos utilizada por este modelo.
     */
    protected $table = 'dh11';

    /**
     * Clave primaria.
     */
    protected $primaryKey = 'codc_categ';
    /**
     * Clave primaria de este modelo es de tipo cadena de caracteres.
     */
    protected $keyType = 'string';


    protected $fillable = [
        'codc_categ', 'equivalencia', 'tipo_escal', 'nro_escal', 'impp_basic', 'codc_dedic', 'sino_mensu', 'sino_djpat', 'vig_caano',
        'vig_cames', 'desc_categ', 'sino_jefat', 'impp_asign', 'computaantig', 'controlcargos', 'controlhoras', 'controlpuntos',
        'controlpresup', 'horasmenanual', 'cantpuntos', 'estadolaboral', 'nivel', 'tipocargo', 'remunbonif', 'noremunbonif', 'remunnobonif',
        'noremunnobonif', 'otrasrem', 'dto1610', 'reflaboral', 'refadm95', 'critico', 'jefatura', 'gastosrepre', 'codigoescalafon',
        'noinformasipuver', 'noinformasirhu', 'imppnooblig', 'aportalao', 'factor_hs_catedra'
    ];
    protected function casts(): array
    {
        return [
            'controlcargos' => 'boolean',
            'controlhoras' => 'boolean',
            'controlpuntos' => 'boolean',
            'controlpresup' => 'boolean',
            'aportalao' => 'boolean',
            'remunbonif' => 'double',
            'noremunbonif' => 'double',
            'remunnobonif' => 'double',
            'noremunnobonif' => 'double',
            'otrasrem' => 'double',
            'dto1610' => 'double',
            'reflaboral' => 'double',
            'refadm95' => 'double',
            'critico' => 'double',
            'jefatura' => 'double',
            'gastosrepre' => 'double',
            'factor_hs_catedra' => 'double',
        ];
    }

    /**
     * Atributos que deben ser convertidos a tipos nativos
     */
    protected $dates = [
        'vig_caano',
        'vig_cames'
    ];


    /**
     * Obtiene el recuento de cargos docentes de educación secundaria.
     *
     * Este método recupera el recuento de cargos docentes de educación secundaria desde la caché. Si la caché
     * no contiene el valor, se calcula consultando la tabla `dh11` para registros donde el campo `codc_categ`
     * está en la categoría `DOCS`, uniéndose con la tabla `dh03`, y devolviendo el recuento.
     *
     * @return int El recuento de cargos docentes de educación secundaria.
     */
    public static function getCargosDoceSecundario(): int
    {
        return Cache::remember('cargos_doce_secundario', 3600, function () {
            return self::whereIn('dh11.codc_categ', self::CATEGORIAS['DOCS'])
                ->join('mapuche.dh03', 'dh11.codc_categ', '=', 'dh03.codc_categ')
                ->count();
        });
    }

    /**
     * Obtiene el recuento de cargos docentes de educación universitaria.
     *
     * Este método recupera el recuento de cargos docentes de educación universitaria desde la caché. Si la caché
     * no contiene el valor, se calcula consultando la tabla `dh11` para registros donde el campo `codc_categ`
     * está en la categoría `DOCU`, uniéndose con la tabla `dh03`, y devolviendo el recuento.
     *
     * @return int El recuento de cargos docentes de educación universitaria.
     */
    public static function getCargosDoceUniversitario(): int
    {
        return Cache::remember('cargos_doce_universitario', 3600, function () {
            return self::whereIn('dh11.codc_categ', self::CATEGORIAS['DOCU'])
                ->join('mapuche.dh03', 'dh11.codc_categ', '=', 'dh03.codc_categ')
                ->count();
        });
    }

    /**
     * Obtiene el recuento de cargos de personal administrativo universitario.
     *
     * Este método recupera el recuento de cargos de personal administrativo universitario desde la caché. Si la caché
     * no contiene el valor, se calcula consultando la tabla `dh11` para registros donde el campo `codc_categ`
     * está en la categoría `AUTU`, uniéndose con la tabla `dh03`, y devolviendo el recuento.
     *
     * @return int El recuento de cargos de personal administrativo universitario.
     */
    public static function getCargosAutoUniversitario(): int
    {
        return Cache::remember('cargos_auto_universitario', 3600, function () {
            return self::whereIn('dh11.codc_categ', self::CATEGORIAS['AUTU'])
                ->join('mapuche.dh03', 'dh11.codc_categ', '=', 'dh03.codc_categ')
                ->count();
        });
    }

    /**
     * Obtiene el recuento de cargos de personal administrativo de educación secundaria.
     *
     * Este método recupera el recuento de cargos de personal administrativo de educación secundaria desde la caché. Si la caché
     * no contiene el valor, se calcula consultando la tabla `dh11` para registros donde el campo `codc_categ`
     * está en la categoría `AUTS`, uniéndose con la tabla `dh03`, y devolviendo el recuento.
     *
     * @return int El recuento de cargos de personal administrativo de educación secundaria.
     */
    public static function getCargosAutoSecundario(): int
    {
        return Cache::remember('cargos_auto_secundario', 3600, function () {
            return self::whereIn('dh11.codc_categ', self::CATEGORIAS['AUTS'])
                ->join('mapuche.dh03', 'dh11.codc_categ', '=', 'dh03.codc_categ')
                ->count();
        });
    }

    /**
     * Obtiene el recuento de cargos de personal no docente.
     *
     * Este método recupera el recuento de cargos de personal no docente desde la caché. Si la caché
     * no contiene el valor, se calcula consultando la tabla `dh11` para registros donde el campo `codc_categ`
     * está en la categoría `NODO`, uniéndose con la tabla `dh03`, y devolviendo el recuento.
     *
     * @return int El recuento de cargos de personal no docente.
     */
    public static function getCargosNoDocente(): int
    {
        return Cache::remember('cargos_no_docente', 3600, function () {
            return self::whereIn('dh11.codc_categ', self::CATEGORIAS['NODO'])
                ->join('mapuche.dh03', 'dh11.codc_categ', '=', 'dh03.codc_categ')
                ->count();
        });
    }

    /**
     * Scope para filtrar por tipo de escalafón.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function scopeOfTipo(Builder $query, string $tipo): Builder
    {
        $categorias = self::getCategoriasPorTipo($tipo);
        return $query->whereIn('codc_categ', $categorias);
    }


    /**
     * Scope para filtrar categorías activas.
     *
     * Este scope se puede utilizar en consultas a la tabla `dh11` para filtrar
     * los registros donde las categorías están activas (es decir, no son nulas).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('vig_caano')
                    ->whereNotNull('vig_cames');
    }


    /**
     * Scope para filtrar por tipo de escalafón.
     *
     * Este scope se puede utilizar en consultas a la tabla `dh11` para filtrar
     * los registros donde el campo `tipo_escal` coincide con el valor proporcionado.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type El tipo de escalafón por el que se desea filtrar.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByScalafon(Builder $query, string $type): Builder
    {
        return $query->where('tipo_escal', $type);
    }

    /**
     * Obtiene los códigos de categoría para un tipo específico.
     *
     * @param string $tipo
     * @return array
     */
    public static function getCategoriasPorTipo(string $tipo): array
    {
        return match ($tipo) {
            'DOCE' => array_merge(self::CATEGORIAS['DOCU'], self::CATEGORIAS['DOCS']),
            'AUTO' => array_merge(self::CATEGORIAS['AUTU'], self::CATEGORIAS['AUTS']),
            default => self::CATEGORIAS[$tipo] ?? [],

        };
    }

    /**
     * Accessor para obtener el estado de mensualización
     */
    public function getIsMensualizedAttribute(): bool
    {
        return $this->sino_mensu === 'S';
    }

    /**
     * Accessor para obtener el estado de jefatura
     */
    public function getHasLeadershipAttribute(): bool
    {
        return $this->sino_jefat === 'S';
    }

    public function dh61(): HasMany
    {
        return $this->hasMany(Dh61::class,'codc_categ','codc_categ')
            ->where('dh61.vig_caano', '=', $this->vig_caano)
            ->where('dh61.vig_cames', '=', $this->vig_cames);
    }

    public function dh31(): BelongsTo
    {
        return $this->belongsTo(dh31::class, 'codc_dedic', 'codc_dedic');
    }

    /**
     * Relación con la tabla de escalafón
     */
    public function dh89(): BelongsTo
    {
        return $this->belongsTo(dh89::class, 'codigoescalafon', 'codigoescalafon');
    }

    public function dh03(): HasMany
    {
        return $this->hasMany(dh03::class, 'codc_categ', 'codc_categ');
    }

    /**
     * Actualiza el campo impp_basic del modelo actual aplicando un porcentaje de incremento.
     *
     * @param float $porcentaje El porcentaje de incremento a aplicar.
     * @return bool Verdadero si la actualización se realizó correctamente, falso en caso contrario.
     */
    public function actualizarImppBasicPorPorcentaje(float $porcentaje): bool
    {
        $service = app(CategoryUpdateServiceInterface::class);
        return $service->updateCategoryWithHistory($this, $porcentaje);
    }
}
