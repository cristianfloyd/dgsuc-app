<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DatabaseHealthWidget extends Widget
{
    protected static string $view = 'filament.widgets.database-health-widget';
    protected int $sortOrder = 2;
    protected static bool $isLazy = true;
    protected string $connection = 'pgsql-mapuche';


    // Actualizamos cada 5 minutos
    public $poolingInterval = 300;

    public function getMetrics()
    {
        // return Cache::remember('database_metrics', 300, function () {
            return [
                'size_db' => $this->getDatabaseSize(),
                'tablas_info' => $this->getMainTablesInfo(),
                'conexion_status' => $this->checkDatabaseConnection(),
                'queries_lentos' => $this->getSlowQueries(),
            ];
        // });
    }

    private function getDatabaseSize(): array
    {
        $dbName = 'desa';
        $size = DB::connection($this->connection)->select("
            SELECT schema_name,
                pg_size_pretty(sum(table_size)::bigint) as formatted_size,
                round(sum(table_size)/1024/1024,2) as size_in_mb
            FROM (
                SELECT pg_catalog.pg_namespace.nspname as schema_name,
                    pg_total_relation_size(pg_catalog.pg_class.oid) as table_size
                FROM pg_catalog.pg_class
                JOIN pg_catalog.pg_namespace ON relnamespace = pg_catalog.pg_namespace.oid
            ) t
            WHERE schema_name = 'mapuche'
            GROUP BY schema_name
            ");
        
        return [
            'value' => $size[0]->size_in_mb ?? 0,
            'unit' => 'MB',
            'status' => $this->getSizeStatus($size[0]->size ?? 0)
        ];
    }

    private function getMainTablesInfo(): array
    {
        return DB::connection($this->connection)->select("
            SELECT relname                                                                 AS nombre,
                PG_SIZE_PRETTY(PG_TOTAL_RELATION_SIZE(schemaname || '.' || relname::TEXT)) AS total_size,
                PG_SIZE_PRETTY(PG_RELATION_SIZE(schemaname || '.' || relname::TEXT))       AS table_size,
                PG_SIZE_PRETTY(PG_TOTAL_RELATION_SIZE(schemaname || '.' || relname::TEXT) -
                               PG_RELATION_SIZE(schemaname || '.' || relname::TEXT))       AS index_size,
                n_live_tup                                                                 AS registros
            FROM pg_stat_user_tables
            WHERE schemaname = 'mapuche'
            ORDER BY PG_TOTAL_RELATION_SIZE(schemaname || '.' || relname::TEXT) DESC
            LIMIT 5");
    }


    private function checkDatabaseConnection(): array
    {
        try {
            $start = microtime(true);
            DB::connection($this->connection)->getPdo();
            $time = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'success',
                'latencia' => $time,
                'mensaje' => 'Conexión establecida'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'latencia' => 0,
                'mensaje' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
    }

    private function getSlowQueries(): array
    {
        // Implementar según la configuración de slow_query_log de MySQL
        return DB::connection($this->connection)->select("
            SELECT
                pid,
                usename as usuario,
                application_name as aplicacion,
                client_addr as ip_cliente,
                state as estado,
                wait_event_type as tipo_espera,
                query as consulta,
                EXTRACT(EPOCH FROM (now() - query_start)) as duracion_segundos
            FROM pg_stat_activity
            WHERE state != 'idle'
                AND query != '<IDLE>'
                AND query NOT ILIKE '%pg_stat_activity%'
                AND EXTRACT(EPOCH FROM (now() - query_start)) > 2
            ORDER BY duracion_segundos DESC
            LIMIT 5
        ");
    }

    private function getSizeStatus($size): string
    {
        if ($size < 1000) return 'success';
        if ($size < 5000) return 'warning';
        return 'danger';
    }
}
