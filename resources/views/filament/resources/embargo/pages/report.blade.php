<x-filament-panels::page>
    <form wire:submit.prevent="generateReport">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-4">
            Generar Reporte
        </x-filament::button>
    </form>

    @if($this->nro_liqui)
        <div class="mt-4">
            {{ $this->table }}
        </div>
    @endif
</x-filament-panels::page>
<script>
    Livewire.on('reportGenerated', () => {
        // Aquí puedes agregar lógica adicional si es necesario
        Livewire.emit('refreshTable');
    });
</script>
