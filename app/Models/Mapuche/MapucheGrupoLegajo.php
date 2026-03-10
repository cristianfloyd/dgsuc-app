<?php

namespace App\Models\Mapuche;

use App\Models\Dh01;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapucheGrupoLegajo extends Model
{
    use MapucheConnectionTrait;

    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'grupo_x_legajo';

    protected $primaryKey;

    protected $fillable = [
        'id_grupo',
        'nro_legaj',
    ];

    /**
     * El grupo al que pertenece este legajo.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Mapuche\MapucheGrupo, $this>
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(MapucheGrupo::class, 'id_grupo', 'id_grupo');
    }

    /**
     * El legajo asociado.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Dh01, $this>
     */
    public function legajo(): BelongsTo
    {
        return $this->belongsTo(Dh01::class, 'nro_legaj', 'nro_legaj');
    }

    #[\Override]
    protected function casts(): array
    {
        return [
            'id_grupo' => 'integer',
            'nro_legaj' => 'integer',
        ];
    }
}
