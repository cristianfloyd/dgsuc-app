<x-filament::widget>
    <x-filament::card>
        <h2 class="text-lg font-medium">Resumen: {{ $idLiqui ?? 'Todos' }}</h2>
        <p>Total Concepto 101: {{ number_format($totalConcepto101, 2) }}</p>
    </x-filament::card>
</x-filament::widget>
