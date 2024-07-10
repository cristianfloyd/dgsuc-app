<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dhc9 extends Model
{
    protected $connection = 'pgsql-mapuche';
    protected $table = 'mapuche.dhc9';
    protected $primaryKey = 'codagrup';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'codagrup',
        'descagrup',
        'codigo_sirhu'
    ];
    //	fk_dh03_dhc9_codc_agrup
}
