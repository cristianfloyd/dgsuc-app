<?php

namespace App\Models;

use App\Traits\HasCompositePrimaryKey;
use App\Traits\MapucheConnectionTrait;
use App\ValueObjects\CategoryIdentifier;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Override;

use function count;

/*
 * (D) HISTORICO-Tabla de Categorias de Empleados
 */
class Dh61 extends Model
{
    use HasCompositePrimaryKey;
    use MapucheConnectionTrait;

    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'dh61';

    /**
     * primaryKey compuesta en formato array.
     *
     * @var array
     */
    protected $primaryKey = ['codc_categ', 'vig_caano', 'vig_cames'];

    protected $fillable = [
        'codc_categ',
        'equivalencia',
        'tipo_escal',
        'nro_escal',
        'impp_basic',
        'codc_dedic',
        'sino_mensu',
        'sino_djpat',
        'vig_caano',
        'vig_cames',
        'desc_categ',
        'sino_jefat',
        'impp_asign',
        'computaantig',
        'controlcargos',
        'controlhoras',
        'controlpuntos',
        'controlpresup',
        'horasmenanual',
        'cantpuntos',
        'estadolaboral',
        'nivel',
        'tipocargo',
        'remunbonif',
        'noremunbonif',
        'remunnobonif',
        'noremunnobonif',
        'otrasrem',
        'dto1610',
        'reflaboral',
        'refadm95',
        'critico',
        'jefatura',
        'gastosrepre',
        'codigoescalafon',
        'noinformasipuver',
        'noinformasirhu',
        'imppnooblig',
        'aportalao',
    ];

    /** @phpstan-ignore property.onlyWritten (Used for Filament route binding) */
    private ?string $virtualId = null;

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
        return (float) $this->impp_basic
            + (float) $this->remunbonif
            + (float) $this->noremunbonif
            + (float) $this->remunnobonif
            + (float) $this->noremunnobonif
            + (float) $this->otrasrem;
    }

    // Método para verificar si requiere declaración jurada patrimonial
    public function requiereDeclaracionJurada(): bool
    {
        return $this->sino_djpat === 'S';
    }

    #[Override]
    public function newQuery()
    {
        /** @phpstan-ignore-next-line return.type */
        return parent::newQuery()->addSelect(
            '*',
            DB::connection($this->getConnectionName())->raw("CONCAT(codc_categ, '-', vig_caano, '-', vig_cames) as id"),
        )->orderByRaw('codc_categ, vig_caano, vig_cames');
    }

    // ########################## Métodos para Filamentphp ##################################

    /**
     * Método para buscar por ID virtual.
     */
    public static function findByVirtualId(string $virtualId): ?self
    {
        $parts = explode('-', $virtualId);

        return static::query()
            ->where('codc_categ', $parts[0])
            ->where('vig_caano', $parts[1])
            ->where('vig_cames', $parts[2])
            ->first();
    }

    public static function find($id): ?self
    {
        if (!$id) {
            return null;
        }

        $parts = explode('-', (string) $id);
        if (count($parts) !== 3) {
            return null;
        }

        return static::query()
            ->where('codc_categ', str_pad(trim($parts[0]), 4))
            ->where('vig_caano', (int) $parts[1])
            ->where('vig_cames', (int) $parts[2])
            ->first();
    }

    /**
     * Método para búsqueda por ID virtual.
     */
    // public static function find($id): Dh61|null
    // {
    //     if (!$id) return null;
    //     $identifier = CategoryIdentifier::fromString($id);
    //     return static::query()
    //         ->where('codc_categ', $identifier->getCategory())
    //         ->where('vig_caano', (int)$identifier->getYear())
    //         ->where('vig_cames', (int)$identifier->getMonth())
    //         ->first();
    // }
    #[Override]
    public function getKeyName(): string
    {
        return 'id';
    }

    /**
     * Obtiene el valor de la clave única para el modelo.
     * devuelve una representación de cadena única de la clave primaria compuesta.
     */
    #[Override]
    public function getKey(): string
    {
        return $this->id;
    }

    /**
     * Establece el nombre de la clave para el modelo.
     *
     * @param string $key El valor de la clave a establecer.
     *
     * @phpstan-ignore method.childReturnType (Custom key handling for Filament)
     */
    #[Override]
    public function setKeyName($key): void
    {
        $this->id = $key;
    }

    #[Override]
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    #[Override]
    public function getRouteKey(): string
    {
        return $this->id;
    }

    #[Override]
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === null) {
            $identifier = CategoryIdentifier::fromString($value);

            return $this
                ->where('codc_categ', $identifier->getCategory())
                ->where('vig_caano', $identifier->getYear())
                ->where('vig_cames', $identifier->getMonth())
                ->firstOrFail();
        }

        return parent::resolveRouteBinding($value, $field);
    }

    public static function resolveRecordRouteBinding(string $key): ?Model
    {
        return static::query()->getModel()::find($key);
    }

    // Scope para filtrar por categoría
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function categoria($query, $categoria)
    {
        return $query->where('codc_categ', str_pad(trim((string) $categoria), 4));
    }

    // Scope para filtrar por vigencia
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function vigencia($query, $ano, $mes)
    {
        return $query
            ->where('vig_caano', $ano)
            ->where('vig_cames', $mes);
    }

    // Método para obtener categorías activas
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function activas($query)
    {
        return $query->where('estadolaboral', 'A');
    }

    // Método para obtener categorías por escalafón
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function porEscalafon($query, $codigoEscalafon)
    {
        return $query->where('codigoescalafon', $codigoEscalafon);
    }

    // public function newQuery()
    // {
    //     $query = parent::newQuery();
    //     $query->addSelect('*');
    //     $query->addSelect(DB::raw("CONCAT(TRIM(codc_categ), '-', vig_caano, '-', vig_cames) as id"));
    //     return $query;
    // }

    // ######################## Accesores y mutadores ########################
    /* ###################################################################### */

    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn(): string => (string) new CategoryIdentifier(
                $this->codc_categ,
                $this->vig_caano,
                $this->vig_cames,
            ),
            set: function (string $value): string {
                $categoryIdentifier = CategoryIdentifier::fromString($value);
                $this->codc_categ = $categoryIdentifier->getCategory();
                $this->vig_caano = $categoryIdentifier->getYear();
                $this->vig_cames = $categoryIdentifier->getMonth();

                return $value;
            },
        );
    }

    protected function codcCateg(): Attribute
    {
        return Attribute::make(
            get: fn(string $value): string => str_pad(trim($value), 4),
            set: fn(string $value): string => str_pad(trim($value), 4),
        );
    }

    #[Override]
    protected function casts(): array
    {
        return [
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
            'aportalao' => 'integer',
        ];
    }
}
