<!-- resources/views/filament/widgets/fiscal-period-selector.blade.php -->
<x-filament::widget>
    <x-filament::card>
        <form wire:submit.prevent="submit">
            {{ $this->form }}
            <x-filament::button type="submit">
                Seleccionar Período Fiscal
            </x-filament::button>
        </form>
    </x-filament::card>
</x-filament::widget>
