<x-filament-panels::page>


    <div class="mt-4">
        @if(!$this->hasResults())
            <div class="p-4 text-sm text-yellow-700 bg-yellow-100 rounded-lg dark:bg-yellow-900 dark:text-yellow-200">
                <p>No hay resultados disponibles. Ejecute los controles para ver el análisis.</p>
            </div>
        @else
            {{ $this->table }}
        @endif
    </div>

    <x-filament::modal />

</x-filament-panels::page>
