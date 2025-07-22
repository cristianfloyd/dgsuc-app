<?php

namespace App\Models\Mapuche;

use Illuminate\Database\Eloquent\Model;

class CheTemp extends Model
{
    public $timestamps = false;

    protected $table = 'che';

    protected $fillable = [
        'codn_area',
        'codn_subar',
        'tipo_conce',
        'codn_grupo',
        'desc_grupo',
        'sino_cheque',
        'importe',
    ];
}
