<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Dhb3 extends Model
{
    use HasFactory;

    use MapucheConnectionTrait;

    // Especifica la tabla asociada al modelo
    protected $table = 'dhb3';

    // Especifica la clave primaria de la tabla
    protected $primaryKey = 'codigo';

    // Indica que la clave primaria no es un incremento automático
    public $incrementing = false;

    // Especifica el tipo de clave primaria
    protected $keyType = 'int';

    // Desactiva las marcas de tiempo automáticas (created_at, updated_at)
    public $timestamps = false;

    // Define los atributos que se pueden asignar en masa
    protected $fillable = [
        'codigo',
        'descripcion',
    ];

    // Define las reglas de validación para los atributos del modelo
    public static function rules()
    {
        return [
            'codigo' => 'required|integer',
            'descripcion' => 'nullable|string|max:50|unique:mapuche.dhb3,descripcion',
        ];
    }

    // Define un alcance local para buscar por descripción
    public function scopeByDescripcion(Builder $query, string $descripcion)
    {
        return $query->where('descripcion', $descripcion);
    }
}
