<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class SicossControl extends Model
{
    use MapucheConnectionTrait;

    protected $table = 'suc.sicoss_controles';
    protected $primaryKey = 'id';
    

    protected $fillable = [
        'periodo',
        'fecha_control',
        'estado',
        'observaciones'
    ];
}
