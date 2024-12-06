<?php

namespace App\Models\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DosubaSinLiquidarModel extends Model
{
    use HasFactory, MapucheConnectionTrait;

    protected $table = 'suc.rep_dosuba_sin_liquidar';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'integer';
    public $timestamps = true;

    protected $fillable = [
        'nro_legaj',
        'nombre_copmpleto',
        'codc_uacad',
        'ultima_liquidacion',
        'periodo_fiscal',
        'fecha_generacion'
    ];

    protected $casts = [
        'nro_legaj' => 'integer',
        'fecha_generacion' => 'datetime',
    ];

    /**
     * Crea la tabla temporal si no existe
     */
    public static function createTableIfNotExists(): void
    {
        $connection = (new static)->getConnection();

        if (!$connection->getSchemaBuilder()->hasTable('suc.rep_dosuba_sin_liquidar')) {
            $connection->statement('
                CREATE TABLE suc.rep_dosuba_sin_liquidar (
                    id SERIAL PRIMARY KEY,
                    nro_legaj INTEGER,
                    nombre_completo VARCHAR(255),
                    codc_uacad VARCHAR(10),
                    ultima_liquidacion INTEGER,
                    periodo_fiscal VARCHAR(6),
                    session_id VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ');

            $connection->statement('CREATE INDEX idx_dosuba_session ON suc.rep_dosuba_sin_liquidar(session_id)');
            $connection->statement('CREATE INDEX idx_dosuba_legajo ON suc.rep_dosuba_sin_liquidar(nro_legaj)');
        }
    }

    /**
     * Limpia los datos de la sesión actual
     */
    public static function clearSessionData(): void
    {
        $sessionId = session()->getId();
        $connection = (new static)->getConnection();

        $connection->table('suc.rep_dosuba_sin_liquidar')
            ->where('session_id', $sessionId)
            ->delete();
    }

    /**
     * Elimina registros antiguos basados en el tiempo de vida de la sesión
     */
    public static function cleanOldRecords(): void
    {
        $sessionLifetime = config('session.lifetime') * 60;
        $connection = (new static)->getConnection();

        $connection->statement("
            DELETE FROM suc.rep_dosuba_sin_liquidar
            WHERE created_at < NOW() - INTERVAL '{$sessionLifetime} seconds'
        ");
    }

    /**
     * Guarda los datos del reporte para la sesión actual
     */
    public static function setReportData($data): void
    {
        try {
            DB::beginTransaction();

            self::createTableIfNotExists();
            self::clearSessionData();
            self::cleanOldRecords();

            $sessionId = session()->getId();

            $dataToInsert = $data->map(function ($item) use ($sessionId) {
                return [
                    'nro_legaj' => $item->nro_legaj,
                    'nombre_completo' => $item->nombre_completo,
                    'codc_uacad' => $item->codc_uacad,
                    'ultima_liquidacion' => $item->ultima_liquidacion,
                    'periodo_fiscal' => $item->periodo_fiscal,
                    'session_id' => $sessionId,
                ];
            })->toArray();

            self::insert($dataToInsert);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al establecer datos del reporte DosubaSinLiquidar: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene los datos del reporte para la sesión actual
     */
    public static function getReportData()
    {
        return self::where('session_id', session()->getId())->get();
    }
}
