<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dh31 extends Model
{
    use MapucheConnectionTrait;
    // protected $connection = 'pgsql-mapuchito';
    protected $table = 'mapuche.dh31';
    public $timestamps = false;
    protected $primaryKey = 'codc_dedic';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'codc_dedic',
        'desc_dedic',
        'cant_horas',
        'tipo_horas'
    ];
    protected $casts = [
        'cant_horas' => 'integer'
    ];
}
