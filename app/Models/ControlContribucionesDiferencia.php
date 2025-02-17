<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class ControlContribucionesDiferencia extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'suc.control_contribuciones_diferencias';

    protected $fillable = [
        'cuil',
        'nro_legaj',
        'contribucionsijpdh21',
        'contribucioninssjpdh21',
        'contribucionsijp',
        'contribucioninssjp',
        'diferencia',
        'fecha_control'
    ];

    protected $casts = [
        'contribucionsijpdh21' => 'decimal:2',
        'contribucioninssjpdh21' => 'decimal:2',
        'contribucionsijp' => 'decimal:2',
        'contribucioninssjp' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'fecha_control' => 'datetime'
    ];
}
