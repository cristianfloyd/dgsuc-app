<?php

namespace App\Models\Reportes;

use App\NroLiqui;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class EmbargoReportModel extends Model
{
    // Definimos la tabla asociada al modelo
    protected $table = 'embargo_reports';

    // Definimos la clave primaria y el tipo de incremento
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';

    // Deshabilitamos timestamps si no los necesitamos
    public $timestamps = false;

    // Definimos los campos asignables en masa
    protected $fillable = [
        'nro_legaj',
        'nombre_completo',
        'codn_conce',
        'importe_descontado',
        'nro_embargo',
        'nro_cargo',
        'caratula',
        'codc_uacad',
        'session_id',
        'nro_liqui',
    ];

    // Definimos los tipos de datos para cada columna
    protected $casts = [
        'id' => 'integer',
        'nro_legaj' => 'integer',
        'codn_conce' => 'integer',
        'importe_descontado' => 'float',
        'nro_embargo' => 'integer',
        'nro_cargo' => 'integer',
    ];

    /**
     * Crea la tabla temporal en la base de datos si no existe
     *
     * @return void
     */
    public static function createTableIfNotExists(): void
    {
        if (!Schema::hasTable('embargo_reports')) {
            Schema::create('embargo_reports', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('nro_legaj')->nullable();
                $table->string('nombre_completo')->nullable();
                $table->integer('codn_conce')->nullable();
                $table->float('importe_descontado')->nullable();
                $table->integer('nro_embargo')->nullable();
                $table->integer('nro_cargo')->nullable();
                $table->string('caratula')->nullable();
                $table->string('codc_uacad')->nullable();
                $table->string('session_id')->nullable();
                $table->integer('nro_liqui')->nullable();
            });
        }
    }

    /**
     * Limpia los datos de la sesión actual en la tabla
     *
     * @return void
     */
    public static function clearSessionData(): void
    {
        $sessionId = session()->getId();

        DB::table('embargo_reports')->where('session_id', $sessionId)->delete();
    }

    /**
     * Establece los datos del reporte para la sesión actual
     *
     * @param array $data
     * @return void
     */
    public static function setReportData(array $data, ?int $nro_liqui = 2): void
    {
        try {
            // Iniciamos una transacción para asegurar la integridad de los datos
            DB::beginTransaction();

            // Creamos la tabla si no existe
            self::createTableIfNotExists();

            // Obtenemos el ID de la sesión actual
            $sessionId = session()->getId();

            // Limpiamos los datos previos de la sesión actual
            self::clearSessionData();

            // Preparamos los datos para inserción
            $dataToInsert = collect($data)->map(function ($item) use ($sessionId, $nro_liqui) {
                $item['session_id'] = $sessionId;
                $item['nro_liqui'] = $nro_liqui;

                return $item;
            })->toArray();

            // Insertamos los datos en la tabla
            self::insert($dataToInsert);

            // Confirmamos la transacción
            DB::commit();
        } catch (\Exception $e) {
            // Revertimos la transacción en caso de error
            DB::rollBack();
            // Manejo de excepciones y registro de errores
            Log::error('Error al establecer los datos del reporte: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene los datos del reporte para la sesión actual
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getReportData()
    {
        $sessionId = session()->getId();
        return self::where('session_id', $sessionId)->get();
    }

    /**
     * Actualiza la estructura de la tabla temporal para la sesión actual
     */
    public static function updateTableStructure(): void
    {
        try {
            Schema::table('embargo_reports', function (Blueprint $table) {
                // Verificamos si la columna no existe antes de agregarla
                if (!Schema::hasColumn('embargo_reports', 'nro_liqui')) {
                    $table->string('nro_liqui')->nullable()->after('session_id');
                }
                // Aquí puedes agregar más columnas según necesites
            });
        } catch (\Exception $e) {
            Log::error('Error al actualizar estructura de la tabla: ' . $e->getMessage());
            throw $e;
        }
    }
}

