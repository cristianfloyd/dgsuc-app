<div class="space-y-4">
    <div class="grid grid-cols-3 gap-4">
        <x-filament::card>
            <div class="text-center">
                <div class="text-2xl font-bold">{{ $resultados->count() }}</div>
                <div class="text-sm text-gray-600">Total Procesados</div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-center">
                <div class="text-2xl font-bold text-success-600">{{ $exitosos }}</div>
                <div class="text-sm text-gray-600">Exitosos</div>
            </div>
        </x-filament::card>

        <x-filament::card>
            <div class="text-center">
                <div class="text-2xl font-bold text-danger-600">{{ $fallidos }}</div>
                <div class="text-sm text-gray-600">Fallidos</div>
            </div>
        </x-filament::card>
    </div>

    <x-filament::table>
        <x-slot name="header">
            <x-filament::table.header-cell>Cargo</x-filament::table.header-cell>
            <x-filament::table.header-cell>Legajo</x-filament::table.header-cell>
            <x-filament::table.header-cell>Estado</x-filament::table.header-cell>
            <x-filament::table.header-cell>Fecha Proceso</x-filament::table.header-cell>
            <x-filament::table.header-cell>Resultado</x-filament::table.header-cell>
        </x-slot>

        @foreach($resultados as $resultado)
            <x-filament::table.row>
                <x-filament::table.cell>{{ $resultado['cargo'] }}</x-filament::table.cell>
                <x-filament::table.cell>{{ $resultado['legajo'] }}</x-filament::table.cell>
                <x-filament::table.cell>
                    <x-filament::badge
                        :color="$resultado['estado'] === 'Procesado' ? 'success' : 'danger'"
                    >
                        {{ $resultado['estado'] }}
                    </x-filament::badge>
                </x-filament::table.cell>
                <x-filament::table.cell>{{ $resultado['fecha_proceso'] }}</x-filament::table.cell>
                <x-filament::table.cell>{{ $resultado['resultado'] }}</x-filament::table.cell>
            </x-filament::table.row>
        @endforeach
    </x-filament::table>
</div>
