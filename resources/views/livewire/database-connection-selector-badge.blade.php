<div class="flex items-center gap-x-2">
    <div
        class="fi-topbar-badge inline-flex items-center gap-1.5 rounded-md border border-gray-200/80 bg-gray-50/80 px-2 py-1 text-xs text-gray-600 transition-colors hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:text-gray-400 dark:hover:bg-white/10"
        title="Conexión de base de datos"
    >
        <svg class="size-3.5 shrink-0 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
        </svg>
        <div class="w-16 min-w-0">
            {{ $this->form }}
        </div>
        <span class="size-1.5 shrink-0 rounded-full bg-green-500 ring-2 ring-white dark:ring-gray-900" aria-hidden="true"></span>
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