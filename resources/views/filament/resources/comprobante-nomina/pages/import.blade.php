<x-filament-panels::page>
    <form wire:submit="import">
        {{ $this->form }}

        <x-filament::button
            type="submit"
            class="mt-4"
        >
            Importar Archivo
        </x-filament::button>
    </form>
</x-filament-panels::page>
