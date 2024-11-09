<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use App\Services\Mapuche\PeriodoFiscalService;
use Illuminate\Database\Eloquent\Casts\Attribute;

// (D) Variable Global: Período Corriente


class Dh99 extends Model
{
    use MapucheConnectionTrait;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'dh99';



    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * La clave primaria compuesta asociada con la tabla.
     *
     * @var array
     */
    protected $primaryKey = ['per_anoct', 'per_mesct'];

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
        'per_anoct',  // Año del período corriente
        'per_mesct',  // Mes del período corriente
        'codc_uacad',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'per_anoct' => 'integer',
        'per_mesct' => 'integer',
        'codc_uacad' => 'string',
    ];

    /**
     * Obtiene el periodo fiscal formateado como YYYYMM
     */
    protected function periodoFiscal(): Attribute
    {
        return Attribute::make(
            get: function() {
                $periodoFiscalService = app(PeriodoFiscalService::class);
                $periodo = $periodoFiscalService->getPeriodoFiscalFromDatabase();
                return $periodo['year'] . $periodo['month'];
            }
        );
    }
}
