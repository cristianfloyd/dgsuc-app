<?php

namespace App\Models\Reportes;

use App\Services\EncodingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Collection;

class EmbargoReportModel extends Model
{
    use MapucheConnectionTrait;

    // Definimos la tabla asociada al modelo
    protected $table = 'suc.embargo_reports';

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
        'nov2_conce',
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

    // ##### mutadores y accesores ##################################

    public function getCodcUacadAttribute($value)
    {
        return trim($value);
    }
    public function geImporteDescontadoAttribute($value)
    {
        return number_format($value, 2, ',', '.');
    }
    public function getCaratulaAttribute($value)
    {
        return EncodingService::toUtf8($value);
    }
    public function setCaratulaAttribute($value)
    {
        $this->attributes['caratula'] = EncodingService::toLatin1($value);
    }
    public function getNombreCompletoAttribute($value)
    {
        return EncodingService::toUtf8($value);
    }
    public function setNombreCompletoAttribute($value)
    {
        $this->attributes['nombre_completo'] = EncodingService::toLatin1($value);
    }

// ######################################################################
    /**
     * Crea la tabla en la base de datos si no existe
     *
     * @return void
     */
    public static function createTableIfNotExists(): void
    {
        $connection = (new static)->getConnection();

        if (!$connection->getSchemaBuilder()->hasTable('suc.embargo_reports')) {
            $connection->statement('
                CREATE TABLE suc.embargo_reports (
                    id SERIAL PRIMARY KEY,
                    nro_legaj INTEGER,
                    nombre_completo VARCHAR(255),
                    codn_conce INTEGER,
                    importe_descontado DECIMAL(15,2),
                    nov2_conce DECIMAL(15,2),
                    nro_embargo INTEGER,
                    nro_cargo INTEGER,
                    caratula VARCHAR(255),
                    codc_uacad VARCHAR(50),
                    session_id VARCHAR(255),
                    nro_liqui INTEGER,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ');

            // Crear índices para optimizar consultas
            $connection->statement('CREATE INDEX idx_embargo_session ON suc.embargo_reports(session_id)');
            $connection->statement('CREATE INDEX idx_embargo_liqui ON suc.embargo_reports(nro_liqui)');
        }
        Log::info('Tabla "embargo_reports" creada o ya existente.');
    }

    /**
     * Limpia los datos de la sesión actual en la tabla
     *
     * @return void
     */
    public static function clearSessionData(): void
    {
        $sessionId = session()->getId();
        $connection = (new static)->getConnection();

        $connection->table('suc.embargo_reports')->where('session_id', $sessionId)->delete();
    }



    /**
     * Elimina los registros antiguos de la tabla 'embargo_reports' que tienen una antigüedad mayor al tiempo de vida de la sesión.
     * Esta función se utiliza para mantener la tabla limpia y evitar el crecimiento excesivo de los datos.
     */
    public static function cleanOldRecords(): void
    {
        $sessionLifetime = config('session.lifetime') * 60; // Convertir minutos a segundos
        $connection = (new static)->getConnection();

        $connection->statement("
            DELETE FROM suc.embargo_reports
            WHERE created_at < NOW() - INTERVAL '{$sessionLifetime} seconds'
        ");
    }



    public static function setReportData(Collection $data, ?int $nro_liqui = 2): void
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
            $dataToInsert = $data->map(function ($item) use ($sessionId, $nro_liqui) {
                return [
                    'nro_legaj' => $item->nro_legaj,
                    'nombre_completo' => $item->nom_demandado,
                    'codn_conce' => $item->codn_conce,
                    'importe_descontado' => $item->impp_conce,
                    'nov2_conce' => $item->nov2_conce,
                    'nro_embargo' => $item->nro_embargo,
                    'nro_cargo' => $item->nro_cargo,
                    'caratula' => $item->caratula,
                    'codc_uacad' => $item->codc_uacad,
                    'session_id' => $sessionId,
                    'nro_liqui' => $nro_liqui
                ];
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

