<x-filament-panels::page>
    {{-- Indicador de carga global --}}
    <div
        wire:loading
        wire:target="updateData"
        class="fixed top-0 left-0 right-0 z-50 bg-primary-500"
    >
        <div class="h-1 w-full overflow-hidden">
            <div class="animate-progress-indeterminate h-1 w-1/3 bg-white"></div>
        </div>
    </div>

    {{-- Overlay de carga --}}
    <div
        x-data
        x-show="$wire.isLoading"
        class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/50"
    >
        <div class="rounded-lg bg-white p-8 shadow-xl dark:bg-gray-800">
            <div class="flex items-center space-x-3">
                <x-filament::loading-indicator class="h-5 w-5" />
                <span class="text-sm">Cargando datos del período...</span>
            </div>
        </div>
    </div>

    {{-- Contenido de la página --}}
    <div @class([
        'opacity-50 pointer-events-none' => $isLoading,
    ])>
        {{ $this->form }}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
