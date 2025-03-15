<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\DynamicConnectionTrait;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use App\Models\AfipMapucheSicossCalculo;
use App\Data\AfipMapucheSicossCalculoData;
use App\Services\DatabaseConnectionService;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\AfipMapucheSicossCalculoRepository;

class EloquentAfipMapucheSicossCalculoRepository implements AfipMapucheSicossCalculoRepository
{
    use DynamicConnectionTrait;

    public function __construct(
        private readonly AfipMapucheSicossCalculo $model
    ) {}

    /**
     * Obtiene la conexión actual para las operaciones del repositorio
     */
    private function getConnection()
    {
        return $this->getConnectionFromTrait();
    }

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
        return $this->model->where('cuil', $cuil)->update($data->toArray());
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
        try {
            // Debug: Registrar información sobre la conexión que se está utilizando
            $connectionName = $this->getConnectionName();
            $sessionConnection = Session::get(DatabaseConnectionService::SESSION_KEY, 'no_establecida');
            $defaultConnection = DatabaseConnectionService::DEFAULT_CONNECTION ?? 'no_definida';
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

            // Dump para visualización en la interfaz (si está en modo debug)
            if (config('app.debug')) {
                dump([
                    'conexión_obtenida' => $connectionName,
                    'conexión_en_sesión' => $sessionConnection,
                    'conexión_predeterminada' => $defaultConnection,
                    'existe_secondary' => $secondaryExists,
                    'tabla' => $this->model->getTable(),
                ]);
            }

            // Ejecutar el truncate
            $this->getConnection()->table($this->model->getTable())->truncate();

            // Registrar éxito
            Log::info('Tabla truncada exitosamente', [
                'tabla' => $this->model->getTable(),
                'conexión' => $connectionName
            ]);
        } catch (\Exception $e) {
            // Registrar el error detallado
            Log::error('Error al truncar la tabla: ' . $e->getMessage(), [
                'conexión_obtenida' => $connectionName ?? 'desconocida',
                'conexión_en_sesión' => $sessionConnection ?? 'desconocida',
                'conexión_predeterminada' => $defaultConnection ?? 'desconocida',
                'tabla' => $this->model->getTable(),
                'excepción' => get_class($e),
                'traza' => $e->getTraceAsString()
            ]);

            // Dump del error para visualización en la interfaz (si está en modo debug)
            if (config('app.debug')) {
                dump([
                    'error' => $e->getMessage(),
                    'conexión_obtenida' => $connectionName ?? 'desconocida',
                    'conexión_en_sesión' => $sessionConnection ?? 'desconocida',
                    'conexión_predeterminada' => $defaultConnection ?? 'desconocida',
                ]);
            }

            // Lanzar una excepción más informativa
            throw new \RuntimeException(
                'Error al truncar la tabla: ' . $e->getMessage() .
                '. Conexión utilizada: ' . ($connectionName ?? 'desconocida') .
                '. Conexión en sesión: ' . ($sessionConnection ?? 'desconocida'),
                0,
                $e
            );
        }
    }
}
