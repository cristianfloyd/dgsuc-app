<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\HasCompositePrimaryKey;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Contracts\Database\Eloquent\Builder;

/*
 * (D) HISTORICO-Tabla de Categorias de Empleados
 * */
class Dh61 extends Model
{
    use MapucheConnectionTrait, HasCompositePrimaryKey;
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'dh61';
    private ?string $virtualId = null;

    /**
     * primaryKey compuesta en formato array
     * @var array
     */
    protected $primaryKey = ['codc_categ', 'vig_caano', 'vig_cames'];
    protected $fillable = ['codc_categ', 'equivalencia', 'tipo_escal', 'nro_escal', 'impp_basic', 'codc_dedic', 'sino_mensu', 'sino_djpat', 'vig_caano', 'vig_cames', 'desc_categ', 'sino_jefat', 'impp_asign', 'computaantig', 'controlcargos', 'controlhoras', 'controlpuntos', 'controlpresup', 'horasmenanual', 'cantpuntos', 'estadolaboral', 'nivel', 'tipocargo', 'remunbonif', 'noremunbonif', 'remunnobonif', 'noremunnobonif', 'otrasrem', 'dto1610', 'reflaboral', 'refadm95', 'critico', 'jefatura', 'gastosrepre', 'codigoescalafon', 'noinformasipuver', 'noinformasirhu', 'imppnooblig', 'aportalao'];

    protected $casts = [
        'impp_basic' => 'decimal:2',
        'impp_asign' => 'decimal:2',
        'controlcargos' => 'boolean',
        'remunbonif' => 'float',
        'noremunbonif' => 'float',
        'remunnobonif' => 'float',
        'noremunnobonif' => 'float',
        'otrasrem' => 'float',
        'dto1610' => 'float',
        'reflaboral' => 'float',
        'refadm95' => 'float',
        'critico' => 'float',
        'jefatura' => 'float',
        'gastosrepre' => 'float',
        'computaantig' => 'integer',
        'controlhoras' => 'integer',
        'controlpuntos' => 'integer',
        'controlpresup' => 'integer',
        'cantpuntos' => 'integer',
        'noinformasipuver' => 'integer',
        'noinformasirhu' => 'integer',
        'imppnooblig' => 'integer',
        'aportalao' => 'integer'
    ];


    // Scope para filtrar por categoría
    public function scopeCategoria($query, $categoria)
    {
        return $query->where('codc_categ', $categoria);
    }

    // Scope para filtrar por vigencia
    public function scopeVigencia($query, $ano, $mes)
    {
        return $query->where('vig_caano', $ano)
                    ->where('vig_cames', $mes);
    }

    // Método para obtener categorías activas
    public function scopeActivas($query)
    {
        return $query->where('estadolaboral', 'A');
    }

    // Método para verificar si es jefatura
    public function esJefatura(): bool
    {
        return $this->sino_jefat === 'S';
    }

    // Método para verificar si está mensualizado
    public function esMensualizado(): bool
    {
        return $this->sino_mensu === 'S';
    }

    // Método para obtener el total de remuneraciones
    public function getTotalRemuneraciones(): float
    {
        return (float)$this->impp_basic +
               (float)$this->remunbonif +
               (float)$this->noremunbonif +
               (float)$this->remunnobonif +
               (float)$this->noremunnobonif +
               (float)$this->otrasrem;
    }

    // Método para verificar si requiere declaración jurada patrimonial
    public function requiereDeclaracionJurada(): bool
    {
        return $this->sino_djpat === 'S';
    }

    // Método para obtener categorías por escalafón
    public function scopePorEscalafon($query, $codigoEscalafon)
    {
        return $query->where('codigoescalafon', $codigoEscalafon);
    }



    public function newQuery()
    {
        return parent::newQuery()->addSelect(
            '*',
            DB::connection($this->getConnectionName())->raw("CONCAT(codc_categ, '-', vig_caano, '-', vig_cames) as id")
        )->orderByRaw('codc_categ, vig_caano, vig_cames');
    }
    public static function diagnosticarConexion()
    {
        $query = static::query();
        dd([
            'conexión' => $query->getConnection()->getName(),
            'sql' => $query->toSql(),
            'registros' => $query->count(),
            'primer_registro' => $query->first()
        ]);
    }




    // ######################## Accesores y mutadores ########################
    /* ###################################################################### */

    protected function id(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->virtualId) {
                    return $this->virtualId;
                }

                if (isset($this->attributes['codc_categ'],
                         $this->attributes['vig_caano'],
                         $this->attributes['vig_cames'])) {
                    return "{$this->attributes['codc_categ']}-{$this->attributes['vig_caano']}-{$this->attributes['vig_cames']}";
                }

                return null;
            },
            set: function ($value) {
                $this->virtualId = $value;

                if ($value) {
                    $parts = explode('-', $value);
                    Log::info("Parts: ", $parts);
                    if (count($parts) === 3) {
                        $this->codc_categ = $parts[0];
                        $this->vig_caano = (int)$parts[1];
                        $this->vig_cames = (int)$parts[2];
                    }
                }
                return $value;
            }
        );
    }

    protected function performInsert(Builder $query): bool
    {
        if (isset($this->attributes['id'])) {
            unset($this->attributes['id']);
        }
        return parent::performInsert($query);
    }

    protected function codcCateg(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? trim($value) : '',
            set: fn ($value) => $value ? trim($value) : ''
        );
    }

    // ########################## Métodos para Filamentphp ##################################
    /**
     * Método para buscar por ID virtual
     */
    public static function findByVirtualId(string $virtualId): ?self
    {
        $parts = explode('-', $virtualId);

        return static::where('codc_categ', $parts[0])
            ->where('vig_caano', $parts[1])
            ->where('vig_cames', $parts[2])
            ->first();
    }

    /**
     * Método para búsqueda por ID virtual
     */
    public static function find($id): Dh61|null
    {
        if (!$id) return null;

        $parts = explode('-', $id);
        if (count($parts) !== 3) return null;

        return static::query()
            ->where('codc_categ', trim($parts[0]))
            ->where('vig_caano', (int)$parts[1])
            ->where('vig_cames', (int)$parts[2])
            ->first();
    }

    /**
     * Obtiene la clave del registro para la tabla de FilamentPHP
     */
    public function getTableRecordKey(): string
    {
        return implode('-', [
            $this->codc_categ,
            $this->vig_caano,
            $this->vig_cames
        ]);
    }

    /**
     * Obtiene la clave del registro para la tabla de FilamentPHP
     * Debe retornar un string, no un array
     */
    public function getKey(): string
    {
        return implode('-', [
            $this->codc_categ,
            $this->vig_caano,
            $this->vig_cames
        ]);
    }

    public function getCompositeKey(): string
    {
        return implode('-', [
            trim($this->codc_categ ?? ''),
            $this->vig_caano ?? '',
            $this->vig_cames ?? ''
        ]);
    }

    public function getRouteKey(): string
    {
        return $this->getCompositeKey();
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === null) {
            [$codc_categ, $vig_caano, $vig_cames] = explode('-', $value);
            return $this->where('codc_categ', $codc_categ)
                       ->where('vig_caano', $vig_caano)
                       ->where('vig_cames', $vig_cames)
                       ->firstOrFail();
        }
        return parent::resolveRouteBinding($value, $field);
    }
}
