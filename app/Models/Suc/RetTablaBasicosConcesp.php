<?php

namespace App\Models\Suc;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

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
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    #[Override]
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
