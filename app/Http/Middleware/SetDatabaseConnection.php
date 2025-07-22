<?php

namespace App\Http\Middleware;

use App\Services\EnhancedDatabaseConnectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetDatabaseConnection
{
    public function __construct(
        protected EnhancedDatabaseConnectionService $connectionService,
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, \Closure $next): Response
    {
        try {
            $connection = $this->connectionService->getCurrentConnection();

            Log::debug('Middleware SetDatabaseConnection', [
                'connection' => $connection,
                'request_path' => $request->path(),
            ]);

            // Verificar que la conexión existe en la configuración
            if (!Config::has("database.connections.{$connection}")) {
                Log::error("La conexión '{$connection}' no existe en la configuración", [
                    'conexiones_disponibles' => array_keys(Config::get('database.connections')),
                ]);

                // Usar una conexión predeterminada segura
                $connection = EnhancedDatabaseConnectionService::DEFAULT_CONNECTION;
                Log::debug("(middleware) Usando conexión predeterminada: {$connection}");
            }

            // Configurar la conexión secundaria según la selección del usuario
            Config::set('database.connections.secondary', Config::get("database.connections.{$connection}"));

            // Asegurar que la conexión está disponible para el trait
            $request->session()->put(EnhancedDatabaseConnectionService::SESSION_KEY, $connection);

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Error en middleware de conexión', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Usar conexión predeterminada en caso de error
            $connection = EnhancedDatabaseConnectionService::DEFAULT_CONNECTION;
            Config::set('database.connections.secondary', Config::get("database.connections.{$connection}"));

            return $next($request);
        }
    }
}
