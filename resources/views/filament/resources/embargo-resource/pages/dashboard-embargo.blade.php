<x-filament::page>
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Panel de Control de Embargos</h1>
        <x-filament::button wire:click="actualizarDatos">Actualizar Datos</x-filament::button>
    </div>
    <!-- Aquí agregar más contenido o widgets relevantes -->
    <livewire:embargo-table />
</x-filament::page>
