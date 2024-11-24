<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class DatabaseConnectionWidget extends Widget
{
    protected static string $view = 'filament.widgets.database-connection-widget';
    protected static ?int $sort = 1; // Orden de aparición
    protected static ?string $section = 'header'; // Ubicación en el dashboard


    public function getConnections(): array
    {
        return [
            'primary' => [
                'name' => config('database.connections.'.config('database.default').'.database'),
                'driver' => config('database.default'),
                'status' => DB::connection()->getPdo() ? 'Conectado' : 'Desconectado'
            ],
            'secondary' => [
                'name' => config('database.connections.secondary.database'),
                'driver' => config('database.connections.secondary.driver'),
                'status' => DB::connection('pgsql-mapuche')->getPdo() ? 'Conectado' : 'Desconectado'
            ]
        ];
    }
}
