<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class DatabaseConnectionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($connection = Session::get('database_connection')) {
            Config::set('database.default', $connection);
        }

        return $next($request);
    }
}
