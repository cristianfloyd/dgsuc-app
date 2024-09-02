<?php

namespace App\Models\Suc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetTablaBasicosConcesp extends Model
{
    use HasFactory;

    protected $connection = 'pgsql_suc';

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'suc.ret_tabla_basicos_conc_esp';

    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<string>
     */
    protected $fillable = [
        'fecha_desde',
        'fecha_hasta',
        'cat_id',
        'conc_liq_id',
        'monto',
        'anios',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'fecha_desde' => 'date',
        'fecha_hasta' => 'date',
        'monto' => 'float',
        'anios' => 'integer',
    ];

    /**
     * Obtiene los registros que coinciden con los criterios de búsqueda.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \DateTime $fecha
     * @param string $catId
     * @param string $concLiqId
     * @param int $anios
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBuscarRegistros($query, \DateTime $fecha, string $catId, string $concLiqId, int $anios)
    {
        return $query->where('fecha_desde', '<=', $fecha)
            ->where('fecha_hasta', '>=', $fecha)
            ->where('cat_id', $catId)
            ->where('conc_liq_id', $concLiqId)
            ->where('anios', $anios);
    }
}
