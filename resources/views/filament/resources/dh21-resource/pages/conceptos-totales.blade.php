<x-filament-panels::page>
    <x-filament::tabs>
        <x-filament::tabs.item wire:click="filterByCodigoEscalafon('AUTO')" :active="$this->codigoEscalafon === 'AUTO'">
            AUTO
        </x-filament::tabs.item>
        <x-filament::tabs.item wire:click="filterByCodigoEscalafon('DOCE')" :active="$this->codigoEscalafon === 'DOCE'">
            DOCE
        </x-filament::tabs.item>
        <x-filament::tabs.item wire:click="filterByCodigoEscalafon('NODO')" :active="$this->codigoEscalafon === 'NODO'">
            NODO
        </x-filament::tabs.item>
    </x-filament::tabs>>
    {{ $this->table }}
</x-filament-panels::page>
