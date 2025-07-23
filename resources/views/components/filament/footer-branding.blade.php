<div class="flex items-center justify-between px-4 py-2 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
    <!-- Lado izquierdo - Información del sistema -->
    <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
        <span class="flex items-center">
            <x-heroicon-o-circle-stack class="h-4 w-4 mr-1" />
            {{ app(\App\Services\DatabaseConnectionService::class)->getCurrentConnectionName() ?: 'Default' }}
        </span>
        
        <span class="flex items-center">
            <x-heroicon-o-cpu-chip class="h-4 w-4 mr-1" />
            Laravel {{ app()->version() }}
        </span>
        
        <span class="flex items-center">
            <x-heroicon-o-sparkles class="h-4 w-4 mr-1" />
            Filament {{ 3.0 }}
        </span>
    </div>

    <!-- Centro - Información de la aplicación -->
    <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
        <span class="font-medium">Sistema de Informes</span>
        <span class="text-xs">v{{ config('app.version', '1.0.0') }}</span>
    </div>

    <!-- Lado derecho - Información del usuario y tiempo -->
    <div class="flex items-center space-x-4 text-sm text-gray-600 dark:text-gray-400">
        @auth
            <span class="flex items-center">
                <x-heroicon-o-user class="h-4 w-4 mr-1" />
                {{ auth()->user()->name }}
            </span>
        @endauth
        
        <span class="flex items-center">
            <x-heroicon-o-clock class="h-4 w-4 mr-1" />
            {{ now()->format('d/m/Y H:i') }}
        </span>
    </div>
</div>
