<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class DatabaseConnectionService
{
    public const SESSION_KEY = 'selected_db_connection';
    public const DEFAULT_CONNECTION = 'pgsql-mapuche';

    /**
     * Obtener todas las conexiones disponibles para el selector
     */
    public function getAvailableConnections(): array
    {
        $connections = Config::get('database.connections');

        // Filtrar solo las conexiones que comienzan con pgsql-
        return collect($connections)
            ->filter(fn ($config, $name) => str_starts_with($name, 'pgsql-'))
            ->keys()
            ->mapWithKeys(fn ($name) => [$name => $this->formatConnectionName($name)])
            ->toArray();
    }

    /**
     * Obtener la conexión actualmente seleccionada
     */
    public function getCurrentConnection(): string
    {
        // Primero intentar obtener de la sesión
        $connection = Session::get(self::SESSION_KEY);

        // Si no está en la sesión, intentar obtener de la cookie
        if (!$connection && Cookie::has(self::SESSION_KEY)) {
            $connection = Cookie::get(self::SESSION_KEY);

            // Si se encontró en la cookie, guardarla en la sesión para futuras solicitudes
            if ($connection) {
                Session::put(self::SESSION_KEY, $connection);
            }
        }

        // Si no se encontró en ningún lado o no es un string válido, usar el valor predeterminado
        if (!is_string($connection) || !array_key_exists($connection, $this->getAvailableConnections())) {
            return self::DEFAULT_CONNECTION;
        }

        return $connection;
    }

    /**
     * Establecer la conexión seleccionada
     */
    public function setConnection(string $connection): void
    {
        if (array_key_exists($connection, $this->getAvailableConnections())) {
            Session::put(self::SESSION_KEY, $connection);

            // La cookie se establece en el componente Livewire para asegurar
            // que se envíe correctamente al navegador
        }
    }

    /**
     * Formatear el nombre de la conexión para mostrar en la UI
     */
    private function formatConnectionName(string $name): string
    {
        $name = str_replace('pgsql-', '', $name);
        return ucfirst($name);
    }

    /**
     * Obtiene el nombre de la conexión actualmente en uso
     *
     * @return string|null
     */
    public function getCurrentConnectionName(): ?string
    {
        return Session::get(self::SESSION_KEY) ?? self::DEFAULT_CONNECTION;
    }
}
