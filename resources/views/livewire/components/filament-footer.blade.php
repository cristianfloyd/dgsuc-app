<footer class="fi-footer flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 shadow-sm">
    <!-- Lado izquierdo - Información del sistema -->
    <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
        <div class="flex items-center gap-1.5 px-2 py-1 bg-gray-50 dark:bg-gray-800 rounded-md">
            <x-heroicon-o-circle-stack class="h-3.5 w-3.5 text-primary-500" />
            <span class="font-medium">{{ $connectionName ?: 'Default' }}</span>
        </div>
        
        <div class="flex items-center gap-1.5 px-2 py-1 bg-gray-50 dark:bg-gray-800 rounded-md">
            <x-heroicon-o-cpu-chip class="h-3.5 w-3.5 text-orange-500" />
            <span>Laravel {{ $laravelVersion }}</span>
        </div>
        
        <div class="flex items-center gap-1.5 px-2 py-1 bg-gray-50 dark:bg-gray-800 rounded-md">
            <x-heroicon-o-sparkles class="h-3.5 w-3.5 text-amber-500" />
            <span>Filament {{ $filamentVersion }}</span>
        </div>
    </div>

    <!-- Centro - Información de la aplicación -->
    <div class="flex items-center gap-2 text-sm">
        <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $appName }}</span>
        <span class="px-2 py-1 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 text-xs font-medium rounded-full">
            v{{ $appVersion }}
        </span>
    </div>

    <!-- Lado derecho - Información del usuario y tiempo -->
    <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
        @auth
            <div class="flex items-center gap-1.5 px-2 py-1 bg-gray-50 dark:bg-gray-800 rounded-md">
                <x-heroicon-o-user class="h-3.5 w-3.5 text-green-500" />
                <span class="font-medium">{{ auth()->user()->name }}</span>
            </div>
        @endauth
        
        <div class="flex items-center gap-1.5 px-2 py-1 bg-gray-50 dark:bg-gray-800 rounded-md" wire:poll.10s="updateTime">
            <x-heroicon-o-clock class="h-3.5 w-3.5 text-blue-500" />
            <span class="font-mono font-medium">{{ $currentTime }}</span>
        </div>
    </div>
</footer> 