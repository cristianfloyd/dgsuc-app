<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpuDisc extends Model
{
    // Especificar la conexión de la base de datos si no es la predeterminada
    use MapucheConnectionTrait;

    // Especificar la tabla asociada al modelo
    protected $table = 'spu_disc';

    // Especificar la clave primaria compuesta
    protected $primaryKey = ['rama', 'disciplina', 'area'];
    public $incrementing = false; // Indicar que la clave primaria no es auto-incremental
    protected $keyType = 'string'; // Indicar que la clave primaria es de tipo string

    // Deshabilitar timestamps si no existen en la tabla
    public $timestamps = false;

    // Especificar los campos que se pueden asignar masivamente
    protected $fillable = [
        'rama',
        'disciplina',
        'area',
        'descripcion',
    ];

    // Sobrescribir el método para manejar la clave primaria compuesta
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }

        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Obtiene la clave primaria compuesta para la consulta de guardado.
     *
     * @param string|null $keyName Nombre de la clave primaria, o null para usar el nombre de la clave primaria del modelo.
     * @return mixed El valor de la clave primaria compuesta.
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }

        return $this->original[$keyName] ?? $this->getAttribute($keyName);
    }

        /**
     * Relación con el modelo Dh03
     */
    public function dh03s(): HasMany
    {
        return $this->hasMany(Dh03::class, 'rama', 'rama')
                ->where('disciplina', $this->disciplina)
                ->where('area', $this->area);
    }
}
