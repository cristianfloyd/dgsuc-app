<!-- resources/views/filament/widgets/fiscal-period-selector.blade.php -->
<x-filament::widget>
    <x-filament::card>
        <form wire:submit.prevent="submit">
            {{ $this->form }}
        </form>
    </x-filament::card>
</x-filament::widget>
