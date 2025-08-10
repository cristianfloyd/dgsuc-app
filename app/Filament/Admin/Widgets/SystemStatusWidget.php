<?php

namespace App\Filament\Admin\Widgets;

use App\Traits\MapucheConnectionTrait;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class SystemStatusWidget extends Widget
{
    use MapucheConnectionTrait;

    protected static string $view = 'filament.widgets.system-status-widget';

    protected static bool $isLazy = true;

    protected static ?int $sort = 3;

    public function getSystemMetrics(): array
    {
        // return Cache::remember('system_metrics', 60, function () {
        return [
            'conexiones' => $this->getDatabaseConnections(),
            'rendimiento' => $this->getPerformanceMetrics(),
            'espacio' => $this->getDatabaseSpace(),
            'actividad' => $this->getActivityMetrics(),
        ];
        // });
    }

    private function getDatabaseConnections(): array
    {
        $connections = DB::connection($this->getConnectionName())
            ->select('
                SELECT count(*) as total_connections
                FROM pg_stat_activity
                WHERE datname = current_database()
            ');

        return [
            'activas' => $connections[0]->total_connections,
            'max' => $this->getMaxConnections(),
        ];
    }

    private function getPerformanceMetrics(): array
    {
        $data = DB::connection($this->getConnectionName())
            ->select('
                SELECT
                    blks_hit::float/(blks_hit+blks_read) as cache_hit_ratio,
                    numbackends as backends,
                    xact_commit as commits,
                    xact_rollback as rollbacks
                FROM pg_stat_database
                WHERE datname = current_database()
            ');
        // dd($data);
        return [
            'cache_hit_ratio' => $data[0]->cache_hit_ratio,
            'backends' => $data[0]->backends,
            'commits' => $data[0]->commits,
            'rollbacks' => $data[0]->rollbacks,
        ];
    }

    private function getDatabaseSpace(): array
    {
        return DB::connection($this->getConnectionName())
            ->select('
                SELECT
                    pg_database_size(current_database()) as db_size,
                    pg_size_pretty(pg_database_size(current_database())) as db_size_pretty
            ');
        // dd($data);
        // return [
        //     'db_size' => $data[0]->db_size,
        //     'db_size_pretty' => $data[0]->db_size_pretty,
        // ];
    }

    private function getActivityMetrics(): array
    {
        return DB::connection($this->getConnectionName())
            ->select('
                SELECT
                    state,
                    count(*) as count,
                    max(extract(epoch from current_timestamp - query_start))::integer as max_duration
                FROM pg_stat_activity
                WHERE datname = current_database()
                GROUP BY state
            ');
    }

    private function getMaxConnections(): int
    {
        return DB::connection($this->getConnectionName())
            ->select('SHOW max_connections')[0]->max_connections;
    }
}
