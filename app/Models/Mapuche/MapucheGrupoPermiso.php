<?php

namespace App\Models\Mapuche;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapucheGrupoPermiso extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'grupo_permisos';
    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'id_grupo',
        'usuario',
        'tipo_permiso'
    ];

    protected $casts = [
        'id_grupo' => 'integer'
    ];

    /**
     * El grupo al que pertenece este permiso
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(MapucheGrupo::class, 'id_grupo', 'id_grupo');
    }
}
