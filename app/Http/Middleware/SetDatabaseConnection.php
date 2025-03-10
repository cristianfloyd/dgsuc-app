<?php

namespace App\Http\Middleware;

use App\Services\DatabaseConnectionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class SetDatabaseConnection
{
    public function __construct(
        protected DatabaseConnectionService $connectionService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $connection = $this->connectionService->getCurrentConnection();
        
        
        // Configurar la conexión secundaria según la selección del usuario
        Config::set('database.connections.secondary', Config::get("database.connections.{$connection}"));
        
        return $next($request);
    }
} 