<?php

namespace App\Models\Mapuche;

use Illuminate\Database\Eloquent\Model;

abstract class MapucheBase extends Model
{
    protected $connection = 'secondary';
    
    // Propiedades y métodos comunes para todos los modelos Mapuche
} 