<x-filament::page>
    <x-filament::tabs>
        @foreach($this->getViewData()['tabs'] as $key => $label)
            <x-filament::tabs.item
                :active="$activeTab === $key"
                wire:click="$set('activeTab', '{{ $key }}')"
            >
                {{ $label }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    @if($activeTab === 'diferencias_aportes' || $activeTab === 'diferencias_contribuciones')
        {{ $this->table }}
    @endif
</x-filament::page>
