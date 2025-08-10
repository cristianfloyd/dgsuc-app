<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlArtDiferencia extends Model
{
    public $timestamps = false;

    protected $table = 'suc.control_art_diferencias';

    protected $fillable = [
        'cuil',
        'art_contrib',
        'calculo_teorico',
        'diferencia',
    ];

    protected $casts = [
        'art_contrib' => 'decimal:2',
        'calculo_teorico' => 'decimal:2',
        'diferencia' => 'decimal:2',
    ];
}
