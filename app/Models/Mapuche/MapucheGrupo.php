<?php

namespace App\Models\Mapuche;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use App\Data\Mapuche\GrupoData;
use App\Traits\MapucheConnectionTrait;
use App\Models\Dh01;

class MapucheGrupo extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'grupo';
    protected $primaryKey = 'id_grupo';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo'
    ];

    protected $casts = [
        'fec_modificacion' => 'datetime',
        'id_grupo' => 'integer'
    ];

    /**
     * Los permisos asociados al grupo
     */
    public function permisos(): HasMany
    {
        return $this->hasMany(MapucheGrupoPermiso::class, 'id_grupo', 'id_grupo');
    }

    /**
     * Los legajos asociados al grupo a través de la tabla pivote
     */
    public function legajos(): BelongsToMany
    {
        return $this->belongsToMany(
            Dh01::class,
            'mapuche.grupo_x_legajo',
            'id_grupo',
            'nro_legaj'
        );
    }

    /**
     * Relación directa con la tabla pivote
     */
    public function grupoLegajos(): HasMany
    {
        return $this->hasMany(MapucheGrupoLegajo::class, 'id_grupo', 'id_grupo');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->fec_modificacion = now();
        });
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeOfTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para buscar por nombre
     */
    public function scopeBuscarPorNombre(Builder $query, string $nombre): Builder
    {
        return $query->where('nombre', 'ILIKE', "%{$nombre}%");
    }

    /**
     * Convertir el modelo a DTO
     */
    public function toDto(): GrupoData
    {
        return GrupoData::from($this);
    }
}
