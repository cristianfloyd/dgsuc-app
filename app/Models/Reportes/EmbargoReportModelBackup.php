<?php

namespace App\Models\Reportes;

use Sushi\Sushi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class EmbargoReportModelBackup extends Model
{
    use Sushi;

    // protected static $booted = false;
    // protected static $instance = null;
    protected $primaryKey = 'id';
    protected $keyType = 'integer';
    public $incrementing = true;


    protected $fillable = [
        'id',
        'nro_legaj',
        'nombre_completo',
        'codn_conce',
        'importe_descontado',
        'nro_embargo',
        'nro_cargo',
        'caratula',
        'codc_uacad'
    ];

    private static array $rows = [];

    // Definimos los tipos de datos para cada columna
    protected $casts = [
        'id' => 'integer',
        'nro_legaj' => 'integer',
        'codn_conce' => 'integer',
        'importe_descontado' => 'float',
        'nro_embargo' => 'integer',
        'nro_cargo' => 'integer'
    ];



    /**
     * Define el esquema de la tabla virtual
     */
    protected function getSchema(): array
    {
        return [
            'id' => 'integer',
            'nro_legaj' => 'integer',
            'nombre_completo' => 'string',
            'codn_conce' => 'integer',
            'importe_descontado' => 'float',
            'nro_embargo' => 'integer',
            'nro_cargo' => 'integer',
            'caratula' => 'string',
            'codc_uacad' => 'string'
        ];
    }


    public function getRows()
    {
        return static::$rows ?: [[
            'id' => 1,
            'nro_legaj' => 0,
            'nombre_completo' => '',
            'codn_conce' => 0,
            'importe_descontado' => 0.00,
            'nro_embargo' => 0,
            'nro_cargo' => 0,
            'caratula' => '',
            'codc_uacad' => ''
        ]];
    }

    // Método para establecer los datos del reporte
    public static function setReportData(array $data): void
    {
        static::$rows = collect($data)->map(function ($item, $index) {
            return array_merge(['id' => $index + 1], $item);
        })->toArray();

        Cache::forget(static::class . ':sushi');

        // Forzamos la recreación del esquema SQLite
        (new static)->getSchema();
    }
}
