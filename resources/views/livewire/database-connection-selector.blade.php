<div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="fi-section-content p-6">
        <h2 class="fi-section-header-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
            Seleccionar Conexión de Base de Datos
        </h2>

        <select wire:model.live="selectedConnection"
        class="fi-select-input block w-full rounded-lg border-0 bg-white py-1.5 text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:bg-white/5 dark:text-white dark:ring-white/10 dark:focus:ring-primary-500">
            @foreach ($availableConnections as $connection)
                <option class="fi-select-option bg-white dark:bg-gray-900 relative cursor-default select-none py-2 pl-3 pr-9 text-gray-900 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                value="{{ $connection }}">{{ $connection }}</option>
            @endforeach
        </select>
    </div>

    <div x-data>
        <!-- Notificaciones -->
        <div x-on:connection-changed.window="$notification({title: 'Conexión Actualizada', description: $event.detail.message})"
            x-on:connection-error.window="$notification({title: 'Error', description: $event.detail.message, variant: 'error'})">
        </div>
    </div>
</div>
