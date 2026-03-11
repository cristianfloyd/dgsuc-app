<?php

namespace App\Models\Reportes;

use App\Traits\MapucheConnectionTrait;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Override;

class DosubaSinLiquidarModel extends Model
{
    use HasFactory;
    use MapucheConnectionTrait;

    public $incrementing = true;

    public $timestamps = true;

    protected $table = 'suc.rep_dosuba_sin_liquidar';

    protected $primaryKey = 'id';

    protected $keyType = 'integer';

    protected $fillable = [
        'nro_legaj',
        'apellido',
        'nombre',
        'nombre_copmpleto',
        'cuil',
        'codc_uacad',
        'ultima_liquidacion',
        'periodo_fiscal',
        'fecha_generacion',
        'embarazada',
        'fallecido',
    ];

    /**
     * Crea la tabla temporal si no existe.
     */
    public static function createTableIfNotExists(): void
    {
        $connection = new self()->getConnectionFromTrait();

        if (!$connection->getSchemaBuilder()->hasTable('suc.rep_dosuba_sin_liquidar')) {
            $connection->statement('
                CREATE TABLE suc.rep_dosuba_sin_liquidar (
                    id SERIAL PRIMARY KEY,
                    nro_legaj INTEGER,
                    apellido VARCHAR(255),
                    nombre VARCHAR(255),
                    cuil VARCHAR(11),
                    codc_uacad VARCHAR(10),
                    ultima_liquidacion INTEGER,
                    periodo_fiscal VARCHAR(6),
                    session_id VARCHAR(255),
                    embarazada BOOLEAN DEFAULT FALSE,
                    fallecido BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ');

            $connection->statement('CREATE INDEX idx_dosuba_session ON suc.rep_dosuba_sin_liquidar(session_id)');
            $connection->statement('CREATE INDEX idx_dosuba_legajo ON suc.rep_dosuba_sin_liquidar(nro_legaj)');
        }
    }

    public static function dropTableIfExists(): void
    {
        $connection = new self()->getConnectionFromTrait();
        dump($connection);
        if ($connection->getSchemaBuilder()->hasTable('suc.rep_dosuba_sin_liquidar')) {
            $connection->statement('DROP TABLE IF EXISTS suc.rep_dosuba_sin_liquidar');
        }
    }

    /**
     * Limpia los datos de la sesión actual.
     */
    public static function clearSessionData(): void
    {
        $sessionId = session()->getId();
        $connection = new self()->getConnection();

        $connection->table('suc.rep_dosuba_sin_liquidar')
            ->where('session_id', $sessionId)
            ->delete();
    }

    /**
     * Elimina registros antiguos basados en el tiempo de vida de la sesión.
     */
    public static function cleanOldRecords(): void
    {
        $sessionLifetime = config('session.lifetime') * 60;
        $connection = new self()->getConnection();

        $connection->statement("
            DELETE FROM suc.rep_dosuba_sin_liquidar
            WHERE created_at < NOW() - INTERVAL '{$sessionLifetime} seconds'
        ");
    }

    /**
     * Guarda los datos del reporte para la sesión actual.
     */
    public static function setReportData($data): void
    {
        try {
            DB::beginTransaction();

            self::createTableIfNotExists();
            self::clearSessionData();
            self::cleanOldRecords();

            $sessionId = session()->getId();

            $dataToInsert = $data->map(fn($item): array => [
                'nro_legaj' => $item['nro_legaj'],
                'apellido' => $item['apellido'],
                'nombre' => $item['nombre'],
                'cuil' => $item['cuil'],
                'codc_uacad' => $item['codc_uacad'],
                'ultima_liquidacion' => $item['ultima_liquidacion'],
                'periodo_fiscal' => $item['periodo_fiscal'],
                'session_id' => $sessionId,
            ])->toArray();

            self::query()->insert($dataToInsert);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al establecer datos del reporte DosubaSinLiquidar: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene los datos del reporte para la sesión actual.
     */
    public static function getReportData()
    {
        return self::query()->where('session_id', session()->getId())->get();
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'nro_legaj' => 'integer',
            'fecha_generacion' => 'datetime',
        ];
    }
}
