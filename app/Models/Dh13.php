<?php

namespace App\Models;

use App\Models\Dh12;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dh13 extends Model
{
    use MapucheConnectionTrait;
    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'dh13';

    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Las claves primarias del modelo.
     *
     * @var array
     */
    protected $primaryKey = ['codn_conce', 'nro_orden_formula'];

    /**
     * Indica si la clave primaria es auto-incrementable.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'codn_conce',
        'desc_calcu',
        'nro_orden_formula',
        'desc_condi',
    ];

    /**
     * Obtiene el Dh12 asociado con este Dh13.
     */
    public function dh12(): BelongsTo
    {
        return $this->belongsTo(Dh12::class, 'codn_conce', 'codn_conce');
    }

    /**
     * Obtiene la clave única para el modelo.
     * Este método devuelve 'id' como nombre de la clave primaria.
     * Esto es necesario para que Filament pueda trabajar con el modelo.
     * @return string
     */
    public function getKeyName()
    {
        return 'id';
    }

    /**
     * Obtiene el valor de la clave única para el modelo.
     * devuelve una representación de cadena única de la clave primaria compuesta.
     * @return mixed
     */
    public function getKey()
    {
        return "{$this->codn_conce}-{$this->nro_orden_formula}";
    }

    /**
     * Establece la clave única para el modelo.
     *
     * @param  mixed  $key
     * @return void
     */
    public function setKeyName($key)
    {
        $this->primaryKey = $key;
    }

    /**
     * Obtiene una nueva instancia de query para el modelo.
     *
     * @return Builder
     */
    public function newQuery()
    {
        return parent::newQuery()->addSelect(
            '*',
            DB::raw("CONCAT(codn_conce, '-', nro_orden_formula) as id")
        );
        // ->orderBy('nro_orden_formula')
        // ->orderBy('codn_conce');
    }

    /**
     * Obtiene el valor de la clave única para rutas.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Recupera el modelo por su clave única.
     *
     * @param  mixed  $key
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function resolveRouteBinding($key, $field = null)
    {
        if ($field === 'id') {
            list($codn_conce, $nro_orden_formula) = explode('-', $key);
            return $this->where('codn_conce', $codn_conce)
                ->where('nro_orden_formula', $nro_orden_formula)
                ->first();
        }
        return parent::resolveRouteBinding($key, $field);
    }

    /**
     * Recupera un modelo por su clave única compuesta.
     *
     * @param string $id La clave única compuesta en el formato "codn_conce-nro_orden_formula".
     * @param array $columns Los campos a recuperar (por defecto, todos los campos).
     * @return \Illuminate\Database\Eloquent\Model|null El modelo encontrado, o null si no se encuentra.
     */
    public function find($id, $columns = ['*'])
    {
        list($codn_conce, $nro_orden_formula) = explode('-', $id);
        return $this->where('codn_conce', $codn_conce)
            ->where('nro_orden_formula', $nro_orden_formula)
            ->first($columns);
    }
}
