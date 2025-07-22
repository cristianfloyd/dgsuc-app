<?php

namespace App\Filament\Admin\Widgets;

use App\Traits\MapucheConnectionTrait;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class ActivityLogWidget extends Widget
{
    use MapucheConnectionTrait;

    protected static string $view = 'filament.widgets.activity-log-widget';
    protected static bool $isLazy = true;
    protected static ?int $sort = 4;

    public function getActivityData(): array
    {
        return [
            'usuarios_activos' => $this->getActiveUsers(),
            'ultimas_acciones' => $this->getLastActions(),
            'estadisticas' => $this->getStatistics(),
        ];
    }

    private function getActiveUsers(): array
    {
        return DB::connection($this->getConnectionName())
            ->select("
                SELECT
                    usename as usuario,
                    application_name,
                    client_addr as ip,
                    backend_start as inicio_sesion
                FROM pg_stat_activity
                WHERE usename IS NOT NULL
                ORDER BY backend_start DESC
                LIMIT 5
            ");
    }

    private function getLastActions(): array
    {
        return DB::connection($this->getConnectionName())
            ->select("
                SELECT
                    schemaname,
                    relname as tabla,
                    last_vacuum as ultimo_vacuum,
                    last_analyze as ultimo_analisis,
                    n_live_tup as registros_vivos,
                    n_dead_tup as registros_muertos
                FROM pg_stat_user_tables
                ORDER BY last_vacuum DESC NULLS LAST
                LIMIT 5
            ");
    }

    private function getStatistics(): array
    {
        return DB::connection($this->getConnectionName())
            ->select("
                SELECT
                    relname as tabla,
                    seq_scan as escaneos_secuenciales,
                    idx_scan as escaneos_indice,
                    n_tup_ins as inserciones,
                    n_tup_upd as actualizaciones,
                    n_tup_del as eliminaciones
                FROM pg_stat_user_tables
                ORDER BY (n_tup_ins + n_tup_upd + n_tup_del) DESC
                LIMIT 5
            ");
    }
}
