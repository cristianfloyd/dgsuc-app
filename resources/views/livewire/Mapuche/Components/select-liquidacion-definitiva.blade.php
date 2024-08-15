<div>
    <select wire:model="liquidacionSeleccionada" class="w-full border-gray-300 rounded-md shadow-sm">
        <option value="">Liquidaciones</option>
        @foreach($liquidaciones as $liquidacion)
            <option value="{{ $liquidacion->nro_liqui }}">
                {{ $liquidacion->desc_liqui }} ({{ $liquidacion->fiscal_period }})
            </option>
        @endforeach
    </select>
</div>
