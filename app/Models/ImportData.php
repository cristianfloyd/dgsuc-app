<?php

namespace App\Models;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportData extends Model
{
    use MapucheConnectionTrait, HasFactory;
    protected $table = 'suc.rep_import_data';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'fecha_registro',
        'email',
        'nombre',
        'usuario_mapuche',
        'dependencia',
        'nro_legaj',
        'nro_cargo',
        'fecha_baja',
        'tipo',
        'observaciones',
    ];
}
