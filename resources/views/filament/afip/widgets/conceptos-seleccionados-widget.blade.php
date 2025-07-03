<x-filament-widgets::widget>
    <x-filament::section>
        <div class="p-4">
            <span class="font-bold">Conceptos seleccionados:</span>
            <div class="mt-2 flex flex-wrap gap-1">
                @forelse($conceptosSeleccionados as $concepto)
                    <span class="inline-block border border-gray-300 dark:border-gray-600 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded px-2 py-1 text-xs">{{ $concepto }}</span>
                @empty
                    <span class="text-gray-500 dark:text-gray-400">Ninguno seleccionado</span>
                @endforelse
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
