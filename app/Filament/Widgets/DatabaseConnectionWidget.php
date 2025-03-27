<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class DatabaseConnectionWidget extends Widget
{
    protected static string $view = 'filament.widgets.database-connection-widget';
    protected static ?int $sort = 1; // Orden de aparición
    protected static ?string $section = 'header'; // Ubicación en el dashboard
    protected static ?string $pollingInterval = '10s'; // Intervalo de actualización
    // Definimos las columnas que queremos mostrar
    protected array $columns = [
        'name' => 'Base de datos',
        'driver' => 'Driver',
        'port' => 'Puerto',
        'status' => 'Estado',
    ];

    public function getConnections(): array
    {
        return [
            'primary' => $this->getConnectionInfo('pgsql'),
            'secondary' => $this->getConnectionInfo('pgsql-prod'),
        ];
    }

    private function getConnectionInfo(string $connection): array
    {
        return array_filter([
            'name' => config("database.connections.$connection.database"),
            'host' => config("database.connections.$connection.host"),
            'port' => config("database.connections.$connection.port"),
            'schema' => config("database.connections.$connection.search_path", 'public'),
            'driver' => config("database.connections$connection.driver"),
            'status' => $this->getConnectionStatus($connection)
        ], fn($value) => !is_null($value));
    }

    private function getConnectionStatus(string $connection): string
    {
        try {
            DB::connection($connection)->getPdo();
            return 'Conectado';
        } catch (\Exception $e) {
            return 'Desconectado';
        }
    }

    public function getColumns(): array
    {
        return $this->columns;
    }
}
