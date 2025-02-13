<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlAportesDiferencia extends Model
{
    protected $table = 'suc.control_aportes_diferencias';
    public $timestamps = false;

    protected $fillable = [
        'cuil',
        'aportesijpdh21',
        'aporteinssjpdh21',
        'diferencia'
    ];

    protected $casts = [
        'aportesijpdh21' => 'decimal:2',
        'aporteinssjpdh21' => 'decimal:2',
        'diferencia' => 'decimal:2'
    ];
}
