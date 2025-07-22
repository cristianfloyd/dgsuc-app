<?php

namespace App\Models;

use App\Data\Dh90Data;
use App\Traits\CargoQueries;
use App\Traits\DatabaseSchema;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent para la tabla mapuche.dh90 que almacena información de cargos asociados.
 *
 * @property int $nro_cargo Número de cargo (clave primaria)
 * @property int|null $nro_cargoasociado Número de cargo asociado (posible relación)
 * @property string|null $tipoasociacion Tipo de asociación (char de 1 caracter)
 *
 * @method static \Database\Factories\Dh90Factory factory()
 * @method static \Illuminate\Database\Eloquent\Builder|Dh90 porTipoAsociacion(string $tipo)
 * @method static \Illuminate\Database\Eloquent\Builder|Dh90 conCargosAsociados()
 */
class Dh90 extends Model
{
    use HasFactory, DatabaseSchema {
        DatabaseSchema::getTable insteadof MapucheConnectionTrait;
    }
    use CargoQueries, MapucheConnectionTrait {
        MapucheConnectionTrait::getConnectionName insteadof DatabaseSchema;
    }

    /**
     * Indica si el modelo debe utilizar timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Nombre de la tabla en la base de datos.
     *
     * @var string
     */
    protected $table = 'dh90';

    /**
     * Esquema de la base de datos donde se encuentra la tabla.
     *
     * @var string
     */
    protected $schema = 'mapuche';

    /**
     * Clave primaria del modelo.
     *
     * @var string
     */
    protected $primaryKey = 'nro_cargo';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nro_cargo',
        'nro_cargoasociado',
        'tipoasociacion',
    ];

    /**
     * Obtiene el Data Object a partir del modelo.
     *
     * @return \App\Data\Dh90Data
     */
    public function toData(): Dh90Data
    {
        return Dh90Data::from($this);
    }

    /**
     * Scope para filtrar por tipo de asociación.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $tipo
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorTipoAsociacion($query, string $tipo)
    {
        return $query->where('tipoasociacion', $tipo);
    }

    /**
     * Scope para obtener registros que tienen cargos asociados.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConCargosAsociados($query)
    {
        return $query->whereNotNull('nro_cargoasociado');
    }

    /**
     * Accessor para obtener el tipo de asociación sin espacios.
     *
     * @return string|null
     */
    public function getTipoAsociacionAttribute($value)
    {
        return $value ? trim($value) : null;
    }

    /**
     * Los atributos que deben convertirse a tipos nativos.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nro_cargo' => 'integer',
            'nro_cargoasociado' => 'integer',
            'tipoasociacion' => 'string',
        ];
    }

    /**
     * Boot del modelo para configurar opciones específicas de PostgreSQL.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        // Configuración específica para PostgreSQL si es necesario
    }
}
