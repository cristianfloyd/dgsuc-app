<x-filament-panels::page>
    <x-filament::tabs>
        @foreach($this->getViewData()['tabs'] as $tabKey => $tabLabel)
            <x-filament::tabs.item
                :active="$this->activeTab === $tabKey"
                wire:click="$set('activeTab', '{{ $tabKey }}')"
            >
                {{ $tabLabel }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    @if($this->activeTab === 'resumen')
        @include('filament.afip.pages.partials.sicoss-resumen', [
            'stats' => $this->cachedStats,
            'resultadosControles' => $this->resultadosControles
        ])
    @else
        <div class="mt-4">
            {{ $this->table }}
        </div>
    @endif
</x-filament-panels::page>
