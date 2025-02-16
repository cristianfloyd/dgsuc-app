<?php

namespace App\Models;

use App\Models\Mapuche\Dh22;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AfipMapucheArt extends Model
{
    use MapucheConnectionTrait;
    protected $table = 'afip_art';
    protected $schema = 'suc';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'cuil_formateado',
        'cuil_original',
        'apellido_y_nombre',
        'nacimiento',
        'sueldo',
        'sexo',
        'nro_legaj',
        'establecimiento',
        'tarea',
        'conce'
    ];

    // Conversión de tipos de datos
    protected $casts = [
        'nacimiento' => 'date',
        'nro_legaj' => 'integer',
        'conce' => 'integer'
    ];

    // Método para obtener el ID para FilamentPHP
    public function getFilamentId(): string
    {
        return $this->getAttribute($this->getKeyName());
    }

    // Método para establecer el ID para FilamentPHP
    public function setFilamentId($value): void
    {
        $this->setAttribute($this->getKeyName(), $value);
    }

    /**
     * Obtiene el nombre completo del usuario.
     *
     * @return string El nombre completo del usuario.
     */
    protected function nombreCompleto(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->apellido_y_nombre
        );
    }

    // Método de búsqueda para FilamentPHP
    public function scopeSearch($query, $search)
    {
        return $query->where('apellido_y_nombre', 'ilike', "%{$search}%")
                    ->orWhere('cuil_formateado', 'ilike', "%{$search}%")
                    ->orWhere('cuil_original', 'ilike', "%{$search}%");
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
     * Metodo para ejecutar la funcion almacenada actualizar_afip_art(nroLiqui int)
     * @param int $nroLiqui
     * @return bool True si la función se ejecutó correctamente, false en caso contrario.
     */
    public function actualizarAfipArt(int $nroLiqui): bool
    {
        if ($this->verificarNroLiqui($nroLiqui)) {
            $result = DB::selectOne('SELECT suc.actualizar_afip_art(?)', [$nroLiqui]);
            return $result->actualizar_afip_art === 'OK';
        }
        return false;
    }

    /**
     * Método para verificar el número de liquidación exista en la tabla mapuche.dh21
     * @param int $nroLiqui
     * @return bool True si nroLiqui existe, false en caso contrario.
     */
    private function verificarNroLiqui($nroLiqui): bool
    {
        return Dh22::verificarNroLiqui($nroLiqui);
    }

    /**
     * Obtiene la relación de pertenencia entre el modelo AfipMapucheArt y el modelo Dh22.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dh22(): BelongsTo
    {
        return $this->belongsTo(Dh22::class, 'nro_legaj', 'nro_legaj');
    }
}
