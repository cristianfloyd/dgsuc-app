<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dhe6 extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    // Indica que la clave primaria no es un incremento automático
    public $incrementing = false;

    // Desactiva las marcas de tiempo automáticas (created_at, updated_at)
    public $timestamps = false;

    // Especifica la tabla asociada al modelo
    protected $table = 'dhe6';

    // Especifica la clave primaria de la tabla
    protected $primaryKey = 'codigocategoriaoa';

    // Especifica el tipo de clave primaria
    protected $keyType = 'string';

    // Define los atributos que se pueden asignar en masa
    protected $fillable = [
        'codigocategoriaoa',
        'desccategoriaoa',
    ];

    // Define las reglas de validación para los atributos del modelo
    public static function rules()
    {
        return [
            'codigocategoriaoa' => 'required|string|size:4',
            'desccategoriaoa' => 'nullable|string|max:255',
        ];
    }

    // Define un alcance local para buscar por descripción
    public function scopeByDescripcion(Builder $query, string $desccategoriaoa)
    {
        return $query->where('desccategoriaoa', $desccategoriaoa);
    }
}
