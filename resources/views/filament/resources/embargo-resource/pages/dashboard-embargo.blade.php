<x-filament::page>
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Panel de Control de Embargos</h1>
        <x-filament::button wire:click="actualizarDatos">Actualizar Datos</x-filament::button>
    </div>
    <!-- Aquí agregar más contenido o widgets relevantes -->
    <x-filament::grid class="grid auto-cols-fr gap-y-2">
        <x-filament::section title="Parámetros">
            <x-filament::card>
                    <div class="grid grid-cols-3 gap-4">
                        <x-filament::input label="Número de Complementarias" wire:model.defer="nroComplementarias" type="text" />
                    </div>
                    <x-filament::button>Guardar</x-filament::button>
            </x-filament::card>
            <form wire:submit.prevent="setParameters">
                <div class="grid grid-cols-3 gap-4">
                    <x-filament::input label="Número de Complementarias" wire:model.defer="nroComplementarias"
                        type="text" class="fi-input"/>
                    <x-filament::input label="Número de Liquidación Definitiva" wire:model.defer="nroLiquiDefinitiva"
                        type="number" />
                    <x-filament::input label="Número de Próxima Liquidación" wire:model.defer="nroLiquiProxima"
                        type="number" />
                    <x-filament::input label="Insertar en DH25" wire:model.defer="insertIntoDh25" />
                </div>
                <x-filament::button type="submit" class="mt-4">Ejecutar Proceso</x-filament::button>
            </form>
        </x-filament::section>
    </x-filament::grid>
</x-filament::page>
