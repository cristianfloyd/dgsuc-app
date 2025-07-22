<?php

namespace App\Models\Mapuche\Bloqueos;

use Illuminate\Database\Eloquent\Model;

class BackupBloqueo extends Model
{
    /**
     * Indica si el modelo debe ser timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'dh03_backup_bloqueos';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nro_liqui',
        'nro_cargo',
        'nro_legaj',
        'fec_baja',
        'fecha_baja_nueva',
        'chkstopliq',
        'tipo_bloqueo',
        'fecha_backup',
        'session_id',
    ];

    /**
     * Los atributos que deben convertirse.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'nro_liqui' => 'integer',
        'nro_cargo' => 'integer',
        'nro_legaj' => 'integer',
        'fec_baja' => 'date',
        'fecha_baja_nueva' => 'date',
        'chkstopliq' => 'boolean',
        'fecha_backup' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
