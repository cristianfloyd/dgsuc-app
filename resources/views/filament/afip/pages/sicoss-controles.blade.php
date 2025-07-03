<x-filament-panels::page>
    <x-filament::tabs>
        @foreach ($this->getViewData()['tabs'] as $tabKey => $tabLabel)
            <x-filament::tabs.item :active="$this->activeTab === $tabKey" wire:click="$set('activeTab', '{{ $tabKey }}')">
                {{ $tabLabel }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    @if ($this->activeTab === 'resumen')
        @include('filament.afip.pages.partials.sicoss-resumen', [
            'stats' => $this->cachedStats,
            'resultadosControles' => $this->resultadosControles,
        ])
    @else
        <div class="mt-4">
            {{ $this->table }}
        </div>
    @endif
    @if (!empty($conceptosSeleccionados))
        <div class="mb-4">
            <span class="font-bold">Conceptos seleccionados:</span>
            <span>
                @foreach ($conceptosSeleccionados as $concepto)
                    <span class="inline-block bg-gray-200 rounded px-2 py-1 text-xs mr-1 mb-1">{{ $concepto }}</span>
                @endforeach
            </span>
        </div>
    @endif
</x-filament-panels::page>
