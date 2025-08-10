<div class="flex items-center gap-2">
    <!-- Selector compacto sin icono -->
    <div class="relative">
        <div class="w-24">
            {{ $this->form }}
        </div>
        
        <!-- Indicador de estado -->
        <div class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-green-500 border border-white dark:border-gray-900"></div>
    </div>

    <!-- Notificaciones -->
    <div x-data>
        <div x-on:connection-changed.window="
            $notification({
                title: 'BD Actualizada',
                description: $event.detail.message,
                variant: 'success'
            })
        "></div>
        
        <div x-on:connection-error.window="
            $notification({
                title: 'Error BD',
                description: $event.detail.message,
                variant: 'error'
            })
        "></div>
    </div>
</div> 