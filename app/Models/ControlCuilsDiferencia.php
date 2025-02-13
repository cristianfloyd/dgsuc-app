<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class ControlCuilsDiferencia extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'suc.control_cuils_diferencias';
    public $timestamps = false;

    protected $fillable = [
        'cuil',
        'origen',
        'fecha_control',
        'connection'
    ];

    protected $casts = [
        'fecha_control' => 'datetime'
    ];
}
