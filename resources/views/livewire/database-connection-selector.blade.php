<div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
    <!-- Icono de base de datos -->
    <div class="flex items-center justify-center w-6 h-6 rounded-md bg-gray-200 dark:bg-gray-700">
        <svg class="w-3.5 h-3.5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
        </svg>
    </div>

    <!-- Selector de conexión -->
    <div class="relative">
        <div class="w-28">
            {{ $this->form }}
        </div>
        
        <!-- Indicador visual de conexión activa -->
        <div class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-green-500 border border-white dark:border-gray-900 shadow-sm"></div>
    </div>

    <!-- Notificaciones Alpine.js -->
    <div x-data>
        <div x-on:connection-changed.window="
            $notification({
                title: 'Conexión Actualizada',
                description: $event.detail.message,
                variant: 'success'
            })
        "></div>
        
        <div x-on:connection-error.window="
            $notification({
                title: 'Error de Conexión',
                description: $event.detail.message,
                variant: 'error'
            })
        "></div>
    </div>
</div>
