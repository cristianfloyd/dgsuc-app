<x-filament-widgets::widget>
    <x-filament::section>
        <div class="p-4">
            <span class="font-bold">Conceptos seleccionados:</span>
            <div class="mt-2">
                @forelse($conceptos as $concepto)
                    <span class="inline-block bg-gray-200 rounded px-2 py-1 text-xs mr-1 mb-1">{{ $concepto }}</span>
                @empty
                    <span class="text-gray-500">Ninguno seleccionado</span>
                @endforelse
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
