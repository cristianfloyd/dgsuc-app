<?php

namespace App\Models\Suc;

use App\Traits\MapucheConnectionTrait;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetTablaBasicosConcesp extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'suc.ret_tabla_basicos_conc_esp';

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
     * Obtiene los registros que coinciden con los criterios de búsqueda.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function buscarRegistros($query, DateTime $fecha, string $catId, string $concLiqId, int $anios)
    {
        return $query->where('fecha_desde', '<=', $fecha)
            ->where('fecha_hasta', '>=', $fecha)
            ->where('cat_id', $catId)
            ->where('conc_liq_id', $concLiqId)
            ->where('anios', $anios);
    }
    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'fecha_desde' => 'date',
            'fecha_hasta' => 'date',
            'monto' => 'float',
            'anios' => 'integer',
        ];
    }
}
