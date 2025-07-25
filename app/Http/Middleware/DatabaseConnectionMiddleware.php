<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class DatabaseConnectionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        // NO cambiar la conexión por defecto en comandos Artisan
        if (app()->runningInConsole() || php_sapi_name() === 'cli') {
            return $next($request);
        }

        // Solo configurar conexión secundaria, NO cambiar database.default
        if ($connection = Session::get('database_connection')) {
            // Verificar que la conexión existe antes de configurarla
            if (Config::has("database.connections.{$connection}")) {
                Config::set('database.connections.secondary', Config::get("database.connections.{$connection}"));
            }
        }

        return $next($request);
    }
}
