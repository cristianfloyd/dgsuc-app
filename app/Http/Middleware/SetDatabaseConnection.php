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
            // NO interferir con comandos Artisan (migrate, etc.)
            if (app()->runningInConsole() || php_sapi_name() === 'cli') {
                return $next($request);
            }

            // Obtener la conexión secundaria seleccionada (o la predeterminada para usuarios nuevos)
            $secondaryConnection = $this->connectionService->getCurrentConnection();

            Log::debug('Middleware SetDatabaseConnection', [
                'secondary_connection' => $secondaryConnection,
                'primary_connection' => config('database.default'),
                'request_path' => $request->path(),
            ]);

            // Verificar que la conexión secundaria existe
            if (!Config::has("database.connections.{$secondaryConnection}")) {
                Log::error("La conexión secundaria '{$secondaryConnection}' no existe", [
                    'conexiones_disponibles' => array_keys(Config::get('database.connections')),
                ]);

                // Usar conexión secundaria predeterminada segura
                $secondaryConnection = EnhancedDatabaseConnectionService::DEFAULT_CONNECTION;
                Log::debug("(middleware) Usando conexión secundaria predeterminada: {$secondaryConnection}");
            }

            // Configurar la conexión secundaria SIN cambiar la conexión por defecto
            Config::set('database.connections.secondary', Config::get("database.connections.{$secondaryConnection}"));

            // Asegurar que la conexión está disponible para el trait y componentes Livewire
            $request->session()->put(EnhancedDatabaseConnectionService::SESSION_KEY, $secondaryConnection);

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Error en middleware de conexión', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Usar conexión secundaria predeterminada en caso de error
            $secondaryConnection = EnhancedDatabaseConnectionService::DEFAULT_CONNECTION;
            Config::set('database.connections.secondary', Config::get("database.connections.{$secondaryConnection}"));

            return $next($request);
        }
    }
}
