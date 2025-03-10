<div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="fi-section-content p-6">
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    BD:
                </span>
                
                <div class="w-40">
                    {{ $this->form }}
                </div>
            </div>

            <div x-data>
                <!-- Notificaciones -->
                <div x-on:connection-changed.window="$notification({title: 'Conexión Actualizada', description: $event.detail.message})"
                    x-on:connection-error.window="$notification({title: 'Error', description: $event.detail.message, variant: 'error'})">
                </div>
            </div>
        </div>
    </div>
</div>
