<?php

namespace App\Models\Mapuche;

use App\Data\Mapuche\GrupoData;
use App\Models\Dh01;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

class MapucheGrupo extends Model
{
    use MapucheConnectionTrait;

    public $timestamps = false;

    protected $table = 'grupo';

    protected $primaryKey = 'id_grupo';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipo',
    ];

    /**
     * Los permisos asociados al grupo.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Mapuche\MapucheGrupoPermiso, $this>
     */
    public function permisos(): HasMany
    {
        return $this->hasMany(MapucheGrupoPermiso::class, 'id_grupo', 'id_grupo');
    }

    /**
     * Los legajos asociados al grupo a través de la tabla pivote.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<\App\Models\Dh01, $this, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function legajos(): BelongsToMany
    {
        return $this->belongsToMany(
            Dh01::class,
            'mapuche.grupo_x_legajo',
            'id_grupo',
            'nro_legaj',
        );
    }

    /**
     * Relación directa con la tabla pivote.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Mapuche\MapucheGrupoLegajo, $this>
     */
    public function grupoLegajos(): HasMany
    {
        return $this->hasMany(MapucheGrupoLegajo::class, 'id_grupo', 'id_grupo');
    }

    /**
     * Scope para filtrar por tipo.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function ofTipo(Builder $query, string $tipo): Builder
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Scope para buscar por nombre.
     */
    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function buscarPorNombre(Builder $query, string $nombre): Builder
    {
        return $query->where('nombre', 'ILIKE', "%{$nombre}%");
    }

    /**
     * Convertir el modelo a DTO.
     */
    public function toDto(): GrupoData
    {
        return GrupoData::from($this);
    }

    /**
     * Boot the model.
     */
    #[Override]
    protected static function boot(): void
    {
        parent::boot();
        static::saving(function ($model): void {
            $model->fec_modificacion = now();
        });
    }

    #[\Override]
    protected function casts(): array
    {
        return [
            'fec_modificacion' => 'datetime',
            'id_grupo' => 'integer',
        ];
    }
}
