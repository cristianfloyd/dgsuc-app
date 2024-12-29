<div class="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
    <x-filament::card>
        <div class="flex flex-col">
            <span class="text-sm font-medium text-gray-500">Total Bruto</span>
            <span class="text-2xl font-bold">{{ $totalBruto }}</span>
        </div>
    </x-filament::card>

    <x-filament::card>
        <div class="flex flex-col">
            <span class="text-sm font-medium text-gray-500">Total Sueldo</span>
            <span class="text-2xl font-bold">{{ $totalSueldo }}</span>
        </div>
    </x-filament::card>

    <x-filament::card>
        <div class="flex flex-col">
            <span class="text-sm font-medium text-gray-500">Total Aportes</span>
            <span class="text-2xl font-bold">{{ $totalAportes }}</span>
        </div>
    </x-filament::card>

    <x-filament::card>
        <div class="flex flex-col">
            <span class="text-sm font-medium text-gray-500">Total Descuentos</span>
            <span class="text-2xl font-bold">{{ $totalDescuentos }}</span>
        </div>
    </x-filament::card>

    <x-filament::card>
        <div class="flex flex-col">
            <span class="text-sm font-medium text-gray-500">Total Imp. Gasto</span>
            <span class="text-2xl font-bold">{{ $totalImpGasto }}</span>
        </div>
    </x-filament::card>

    <x-filament::card>
        <div class="flex flex-col">
            <span class="text-sm font-medium text-gray-500">Registros</span>
            <span class="text-2xl font-bold">{{ $cantidadRegistros }}</span>
        </div>
    </x-filament::card>
</div>
