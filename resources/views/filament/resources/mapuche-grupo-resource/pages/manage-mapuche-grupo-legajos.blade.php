<x-filament-panels::page>
    <x-filament-panels::header>
        <x-slot name="heading">
            Administrar Legajos del Grupo: {{ $record->nombre }}
        </x-slot>

        <x-slot name="subheading">
            {{ $record->descripcion }}
        </x-slot>
    </x-filament-panels::header>

    {{ $this->table }}
</x-filament-panels::page>
