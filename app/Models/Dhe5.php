<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dhe5 extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    // Indica que la clave primaria no es un incremento automático
    public $incrementing = false;

    // Desactiva las marcas de tiempo automáticas (created_at, updated_at)
    public $timestamps = false;

    // Especifica la tabla asociada al modelo
    protected $table = 'dhe5';

    // Especifica la clave primaria de la tabla
    protected $primaryKey = 'codigoescalafonoa';

    // Especifica el tipo de clave primaria
    protected $keyType = 'string';

    // Define los atributos que se pueden asignar en masa
    protected $fillable = [
        'codigoescalafonoa',
        'descescalafonoa',
    ];

    // Define las reglas de validación para los atributos del modelo
    public static function rules()
    {
        return [
            'codigoescalafonoa' => 'required|string|size:4',
            'descescalafonoa' => 'nullable|string|max:255',
        ];
    }

    // Define un alcance local para buscar por descripción
    public function scopeByDescripcion(Builder $query, string $descescalafonoa)
    {
        return $query->where('descescalafonoa', $descescalafonoa);
    }
}
