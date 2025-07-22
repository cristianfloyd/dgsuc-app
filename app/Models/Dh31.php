<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;

class Dh31 extends Model
{
    use MapucheConnectionTrait;

    public $timestamps = false;

    public $incrementing = false;

    protected $table = 'dh31';

    protected $primaryKey = 'codc_dedic';

    protected $keyType = 'string';

    protected $fillable = [
        'codc_dedic',
        'desc_dedic',
        'cant_horas',
        'tipo_horas',
    ];

    protected $casts = [
        'cant_horas' => 'integer',
    ];
}
