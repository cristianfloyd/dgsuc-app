<?php

namespace App\Filament\Admin\Widgets;

use App\Traits\MapucheConnectionTrait;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class MaintenanceLogsWidget extends Widget
{
    use MapucheConnectionTrait;

    protected static string $view = 'filament.widgets.maintenance-logs-widget';

    protected static bool $isLazy = true;

    protected static ?int $sort = 5;

    public function getMaintenanceLogs(): array
    {
        return [
            'procesos' => $this->getProcesosPendientes(),
            'backups' => $this->getEstadoBackups(),
            'indices' => $this->getEstadoIndices(),
            'vacuums' => $this->getVacuumStatus(),
        ];
    }

    private function getProcesosPendientes(): array
    {
        return DB::connection($this->getConnectionName())
            ->select("
                SELECT pid,
                       query,
                       state,
                       age(clock_timestamp(), query_start) as duration
                FROM pg_stat_activity
                WHERE state != 'idle'
                ORDER BY duration DESC
                LIMIT 5
            ");
    }

    private function getEstadoBackups(): array
    {
        return DB::connection($this->getConnectionName())
            ->select('
                SELECT
                    1 as ultimo_backup,
                    1 as ultima_recuperacion,
                    1 as en_recuperacion
                    --pg_last_xlog_receive_location() as ultimo_backup,
                    --pg_last_xlog_replay_location() as ultima_recuperacion,
                    --pg_is_in_recovery() as en_recuperacion
            ');
    }

    private function getEstadoIndices(): array
    {
        return DB::connection($this->getConnectionName())
            ->select('
                SELECT
                    schemaname,
                    relname as tablename,
                    indexrelname,
                    pg_size_pretty(pg_relation_size(indexrelid)) as index_size
                FROM pg_stat_user_indexes
                ORDER BY pg_relation_size(indexrelid) DESC
                LIMIT 5
            ');
    }

    private function getVacuumStatus(): array
    {
        return DB::connection($this->getConnectionName())
            ->select('
                SELECT
                    relname,
                    last_vacuum,
                    last_autovacuum,
                    n_dead_tup as tuplas_muertas
                FROM pg_stat_user_tables
                ORDER BY n_dead_tup DESC
                LIMIT 5
            ');
    }
}
