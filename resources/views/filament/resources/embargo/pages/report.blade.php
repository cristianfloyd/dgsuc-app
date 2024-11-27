<x-filament-panels::page>
    <form wire:submit="generateReport">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-4">
            Generar Reporte
        </x-filament::button>
    </form>

    @if($reportData)
        <div class="mt-4">
            <x-filament-tables::table>
                <x-slot name="header">
                    <x-filament-tables::header-cell>Legajo</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Nombre</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Concepto</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Importe</x-filament-tables::header-cell>
                </x-slot>

                @foreach($reportData as $legajo)
                    @foreach($legajo as $embargo)
                        <x-filament-tables::row>
                            <x-filament-tables::cell>{{ $embargo['nro_legaj'] }}</x-filament-tables::cell>
                            <x-filament-tables::cell>{{ $embargo['nombre_completo'] }}</x-filament-tables::cell>
                            <x-filament-tables::cell>{{ $embargo['codn_conce'] }}</x-filament-tables::cell>
                            <x-filament-tables::cell>
                                {{ number_format($embargo['importe_descontado'], 2) }}
                            </x-filament-tables::cell>
                        </x-filament-tables::row>
                    @endforeach
                @endforeach
            </x-filament-tables::table>
        </div>
    @endif
</x-filament-panels::page>
