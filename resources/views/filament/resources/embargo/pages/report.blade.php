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
                    <x-filament-tables::header-cell>Cargo</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Nombre</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Unidad Acad</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Caratula</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Nro. Embargo</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Concepto</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Importe</x-filament-tables::header-cell>
                </x-slot>

                @foreach($reportData as $legajo)
                    @foreach($legajo as $embargo)
                        <x-filament-tables::row>
                            <x-filament-tables::cell>{{ $embargo['nro_legaj'] }}</x-filament-tables::cell>
                            <x-filament-tables::cell>{{ $embargo['nro_cargo'] }}</x-filament-tables::cell>
                            <x-filament-tables::cell>{{ $embargo['nombre_completo'] }}</x-filament-tables::cell>
                            <x-filament-tables::cell>{{ $embargo['codc_uacad'] }}</x-filament-tables::cell>
                            <x-filament-tables::cell class="truncate max-w-16" title="{{ $embargo['caratula'] }}">
                                {{ $embargo['caratula'] }}</x-filament-tables::cell>
                            <x-filament-tables::cell>{{ $embargo['nro_embargo'] }}</x-filament-tables::cell>
                            <x-filament-tables::cell>{{ $embargo['codn_conce'] }}</x-filament-tables::cell>
                            <x-filament-tables::cell>
                                {{ number_format($embargo['importe_descontado'], 2) }}
                            </x-filament-tables::cell>
                        </x-filament-tables::row>
                    @endforeach
                @endforeach
            </x-filament-tables::table>
            <div class="flex justify-end mb-4">
                <x-filament::input.select
                    wire:model.live="perPage"
                    class="w-32"
                >
                    <option value="10">10 por página</option>
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                    <option value="100">100 por página</option>
                </x-filament::input.select>
            </div>
            <div class="mt-4">
                {{ $this->getTableRecords()->links() }}
            </div>
        </div>
    @endif
</x-filament-panels::page>
