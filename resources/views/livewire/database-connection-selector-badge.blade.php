<div class="flex items-center">
    <!-- Badge con selector -->
    <div class="relative inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200">
        <!-- Icono pequeño -->
        <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
        </svg>
        
        <!-- Selector -->
        <div class="w-20">
            {{ $this->form }}
        </div>
        
        <!-- Indicador de estado -->
        <div class="w-1.5 h-1.5 rounded-full bg-green-500 border border-white dark:border-gray-900"></div>
    </div>

    <!-- Notificaciones -->
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