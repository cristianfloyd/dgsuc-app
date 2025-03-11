<?php

namespace App\Models\Mapuche;

use App\Traits\DynamicConnectionTrait;
use Illuminate\Database\Eloquent\Model;

abstract class MapucheBase extends Model
{
    use DynamicConnectionTrait;

    // Propiedades y métodos comunes para todos los modelos Mapuche
}
