<?php

namespace App\Models\Mapuche;

use App\Models\Dh01;
use App\Models\Dh03;
use App\Models\Mapuche\Catalogo\Dl09;
use App\Models\Mapuche\Catalogo\Dl10;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class Dh05 extends Model
{
    use MapucheConnectionTrait;

    public $incrementing = false;

    public $timestamps = false;

    protected $table = 'dh05';

    protected $primaryKey = 'nro_licencia';

    protected $keyType = 'int';

    // Especificar los campos que se pueden asignar masivamente
    protected $fillable = [
        'nro_licencia', 'nro_legaj', 'nro_cargo', 'fec_desde', 'fec_hasta', 'fecha_finalorig',
        'nrovarlicencia', 'observacion', 'tipo_norma_alta', 'emite_norma_alta', 'fecha_norma_alta',
        'nro_norma_alta', 'tipo_norma_baja', 'emite_norma_baja', 'fecha_norma_baja', 'nro_norma_baja',
        'mes_actualizacion', 'anio_actualizacion', 'trab_sab', 'trab_dom', 'presentismo',
        'codmotivolic', 'trab_fer',
    ];

    /**
     * Relación con el modelo Dh01.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Dh01, $this>
     */
    public function dh01(): BelongsTo
    {
        return $this->belongsTo(Dh01::class, 'nro_legaj', 'nro_legaj');
    }

    /**
     * Relación con el modelo Dh03.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Dh03, $this>
     */
    public function dh03(): BelongsTo
    {
        return $this->belongsTo(Dh03::class, 'nro_cargo', 'nro_cargo');
    }

    /**
     * Relación con el modelo Dl09 (tipo_norma_alta).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Mapuche\Catalogo\Dl09, $this>
     */
    public function dl09(): BelongsTo
    {
        return $this->belongsTo(Dl09::class, 'tipo_norma_alta', 'nombre_tipo_norma');
    }

    /**
     * Relación con el modelo Dl10 (emite_norma_alta).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Mapuche\Catalogo\Dl10, $this>
     */
    public function dl10(): BelongsTo
    {
        return $this->belongsTo(Dl10::class, 'emite_norma_alta', 'quien_emite_norma');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'nro_licencia' => 'integer',
            'nro_legaj' => 'integer',
            'nro_cargo' => 'integer',
            'fec_desde' => 'date',
            'fec_hasta' => 'date',
            'fecha_finalorig' => 'date',
            'nrovarlicencia' => 'integer',
            'fecha_norma_alta' => 'date',
            'nro_norma_alta' => 'integer',
            'fecha_norma_baja' => 'date',
            'nro_norma_baja' => 'integer',
            'mes_actualizacion' => 'integer',
            'anio_actualizacion' => 'integer',
            'trab_sab' => 'integer',
            'trab_dom' => 'integer',
            'presentismo' => 'integer',
            'codmotivolic' => 'integer',
            'trab_fer' => 'integer',
        ];
    }
}
