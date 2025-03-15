<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class EnhancedDatabaseConnectionService
{
    public const SESSION_KEY = 'selected_db_connection';
    public const COOKIE_KEY = 'db_connection';
    public const CACHE_KEY = 'user_db_connection_';
    public const DEFAULT_CONNECTION = 'pgsql-prod';

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
     * Obtener la conexión actualmente seleccionada con múltiples capas de persistencia
     */
    public function getCurrentConnection(): string
    {
        // Estrategia de múltiples capas para obtener la conexión
        $userId = auth()->guard('web')->id() ?? session()->getId();
        $cacheKey = self::CACHE_KEY . $userId;

        // Intentar obtener de la sesión primero (memoria)
        $connection = Session::get(self::SESSION_KEY);

        // Si no está en la sesión, intentar obtener de la caché (persistente)
        if (!$connection) {
            $connection = Cache::get($cacheKey);

            // Si se encontró en la caché, guardarla en la sesión
            if ($connection) {
                Session::put(self::SESSION_KEY, $connection);
                Log::debug("Conexión recuperada de caché", ['connection' => $connection]);
            }
        }

        // Si no está en la caché, intentar obtener de la cookie
        if (!$connection && Cookie::has(self::COOKIE_KEY)) {
            $connection = Cookie::get(self::COOKIE_KEY);

            // Si se encontró en la cookie, guardarla en la sesión y caché
            if ($connection) {
                Session::put(self::SESSION_KEY, $connection);
                Cache::put($cacheKey, $connection, now()->addDays(30));
                Log::debug("Conexión recuperada de cookie", ['connection' => $connection]);
            }
        }

        // Si no se encontró en ningún lado, usar el valor predeterminado
        if (!is_string($connection) || !array_key_exists($connection, $this->getAvailableConnections())) {
            $connection = self::DEFAULT_CONNECTION;
            Log::debug("Usando conexión predeterminada", ['connection' => $connection]);
        }

        return $connection;
    }

    /**
     * Establecer la conexión seleccionada en múltiples capas de persistencia
     */
    public function setConnection(string $connection): void
    {
        if (array_key_exists($connection, $this->getAvailableConnections())) {
            $userId = auth()->guard('web')->id() ?? session()->getId();
            $cacheKey = self::CACHE_KEY . $userId;

            // Guardar en sesión (memoria)
            Session::put(self::SESSION_KEY, $connection);

            // Guardar en caché (persistente)
            Cache::put($cacheKey, $connection, now()->addDays(30));

            // Configurar la cookie (se enviará en la respuesta)
            Cookie::queue(
                self::COOKIE_KEY,
                $connection,
                60*24*30 // 30 días
            );

            Log::debug("Conexión establecida en múltiples capas", [
                'connection' => $connection,
                'session' => true,
                'cache' => true,
                'cookie' => true
            ]);
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
}
