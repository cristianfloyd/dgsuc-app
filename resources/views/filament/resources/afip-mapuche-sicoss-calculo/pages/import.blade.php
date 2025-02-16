<x-filament-panels::page>
    <form wire:submit="import">
        {{ $this->form }}

        <div class="mt-4">
            <x-filament::button type="submit">
                Importar
            </x-filament::button>
        </div>
    </form>

    @if($importProgress > 0)
        <div class="mt-4">
            <div class="text-sm font-medium">
                Progreso: {{ $importProgress }}%
            </div>
            <div class="mt-1 h-2 bg-gray-200 rounded-full">
                <div class="h-2 bg-primary-600 rounded-full" style="width: {{ $importProgress }}%"></div>
            </div>
            <div class="mt-2 text-sm text-gray-600">
                Registros procesados: {{ $processedRecords }}
            </div>
        </div>
    @endif
</x-filament-panels::page>
