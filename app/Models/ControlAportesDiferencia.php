<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class ControlAportesDiferencia extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'suc.control_aportes_diferencias';
    public $timestamps = false;

    protected $fillable = [
        'cuil',
        'codc_uacad',
        'caracter',
        'aportesijpdh21',
        'aporteinssjpdh21',
        'diferencia',
        'fecha_control',
        'connection'
    ];

    protected $casts = [
        'aportesijpdh21' => 'decimal:2',
        'aporteinssjpdh21' => 'decimal:2',
        'diferencia' => 'decimal:2'
    ];
}
