<x-filament-panels::page>
    <x-filament-panels::form wire:submit="generate">
        {{ $this->form }}

        <div class="flex justify-end mt-6">
            <x-filament::button type="submit" size="lg" wire:loading.attr="disabled">
                Generar Comprobantes

                <x-slot:icon>
                    <x-heroicon-m-document-check class="w-5 h-5" />
                </x-slot>
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>
