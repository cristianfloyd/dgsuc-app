<?php

namespace App\Models;

use App\Enums\LegajoCargo;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportDataModel extends Model
{
    use MapucheConnectionTrait, HasFactory;
    protected $table = 'suc.rep_bloqueos_import';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'nro_liqui',
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
        'chkstopliq'
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
        'fecha_baja' => 'datetime',
        'chkstopliq' => 'boolean',
    ];

    /* ######## ATTRIBUTES ########################################## */
    public function legajoCargo(): Attribute
    {
        return Attribute::make(
            get: fn() => LegajoCargo::from($this->nro_legaj, $this->nro_cargo),
        );
    }

    /* ##############################################################
    ####  RELACIONES ############################################### */

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Dh03::class, 'nro_cargo', 'nro_cargo');
    }
}
