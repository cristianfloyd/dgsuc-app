<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Data\AfipMapucheSicossCalculoData;
use App\Models\AfipMapucheSicossCalculo;
use App\Repositories\Contracts\AfipMapucheSicossCalculoRepository;
use App\Services\DatabaseConnectionService;
use App\Traits\DynamicConnectionTrait;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class EloquentAfipMapucheSicossCalculoRepository implements AfipMapucheSicossCalculoRepository
{
    use DynamicConnectionTrait;

    public function __construct(
        private readonly AfipMapucheSicossCalculo $model,
    ) {}

    public function find(string $cuil): ?AfipMapucheSicossCalculoData
    {
        $model = $this->model->where('cuil', $cuil)->first();

        return $model ? $model->toData() : null;
    }

    public function create(AfipMapucheSicossCalculoData $data): AfipMapucheSicossCalculoData
    {
        $model = $this->model->create($data->toArray());

        return $model->toData();
    }

    public function update(string $cuil, AfipMapucheSicossCalculoData $data): bool
    {
        return (bool) $this->model->where('cuil', $cuil)->update($data->toArray());
    }

    public function delete(string $cuil): bool
    {
        return $this->model->where('cuil', $cuil)->delete();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function truncate(): void
    {
        $connectionName = 'desconocida';
        $sessionConnection = 'no_establecida';
        $defaultConnection = DatabaseConnectionService::DEFAULT_CONNECTION;

        try {
            // Debug: Registrar información sobre la conexión que se está utilizando
            $connectionName = $this->getConnectionName();
            $sessionConnection = Session::get(DatabaseConnectionService::SESSION_KEY, 'no_establecida');
            $secondaryExists = Config::has('database.connections.secondary') ? 'sí' : 'no';

            // Registrar en el log para debugging
            Log::debug('Información de depuración antes de truncate', [
                'conexión_obtenida' => $connectionName,
                'conexión_en_sesión' => $sessionConnection,
                'conexión_predeterminada' => $defaultConnection,
                'existe_secondary' => $secondaryExists,
                'tabla' => $this->model->getTable(),
                'todas_las_conexiones' => array_keys(Config::get('database.connections')),
            ]);

            // Ejecutar el truncate
            $this->getConnection()->table($this->model->getTable())->truncate();

            // Registrar éxito
            Log::info('Tabla truncada exitosamente', [
                'tabla' => $this->model->getTable(),
                'conexión' => $connectionName,
            ]);
        } catch (Exception $e) {
            // Registrar el error detallado
            Log::error('Error al truncar la tabla: ' . $e->getMessage(), [
                'conexión_obtenida' => $connectionName,
                'conexión_en_sesión' => $sessionConnection,
                'conexión_predeterminada' => $defaultConnection,
                'tabla' => $this->model->getTable(),
                'excepción' => $e::class,
                'traza' => $e->getTraceAsString(),
            ]);

            // Dump del error para visualización en la interfaz (si está en modo debug)
            if (config('app.debug')) {
                dump([
                    'error' => $e->getMessage(),
                    'conexión_obtenida' => $connectionName,
                    'conexión_en_sesión' => $sessionConnection,
                    'conexión_predeterminada' => $defaultConnection,
                ]);
            }

            // Lanzar una excepción más informativa
            $mensaje = 'Error al truncar la tabla: ' . $e->getMessage()
                . '. Conexión utilizada: ' . $connectionName
                . '. Conexión en sesión: ' . $sessionConnection;
            throw new RuntimeException($mensaje, 0, $e);
        }
    }

    /**
     * Obtiene la conexión actual para las operaciones del repositorio.
     */
    private function getConnection()
    {
        return $this->getConnectionFromTrait();
    }
}
