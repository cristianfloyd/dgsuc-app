<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class ControlCuilsDiferencia extends Model
{
    use MapucheConnectionTrait;

    public $timestamps = false;

    protected $table = 'suc.control_cuils_diferencias';

    protected $fillable = [
        'cuil',
        'origen',
        'fecha_control',
        'connection',
    ];

    protected $casts = [
        'fecha_control' => 'datetime',
    ];
}
