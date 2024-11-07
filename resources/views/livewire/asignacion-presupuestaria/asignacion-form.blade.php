<div class="p-6 space-y-6">
    <!-- Formulario de busqueda -->
    <div class="flex justify-between items-center">

        <div class="w-1/3">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Buscar por número de cargo..."
                class="w-full rounded-md border-gray-300 shadow-sm"
            >
        </div>
        <div>
            <span class="text-sm font-medium">
                Total Asignado: {{ number_format($totalAllocated, 2) }}%
            </span>
            <span class="ml-4 text-sm font-medium">
                Disponible: {{ number_format($availablePercentage, 2) }}%
            </span>
        </div>
    </div>

    <!-- Formulario de imputación -->
    <form wire:submit.prevent="save" class="grid grid-cols-3 gap-4">


        <div>
            <label class="block text-sm font-medium text-gray-700">Cargo</label>
            <input
                type="number"
                wire:model="nro_cargo"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            >
            @error('nro_cargo')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Porcentaje</label>
            <input
                type="number"
                wire:model="porc_ipres"
                step="0.01"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
            >
            @error('porc_ipres')
                <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        <!-- Otros campos del formulario -->

        <div class="col-span-3">
            <button
                type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
            >
                {{ $isEditing ? 'Actualizar' : 'Guardar' }}
            </button>
        </div>
    </form>

    <!-- Tabla de imputaciones -->
    <div class="mt-8">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th wire:click="$set('sortField', 'nro_cargo')" class="cursor-pointer">
                        Cargo
                    </th>
                    <th wire:click="$set('sortField', 'porc_ipres')" class="cursor-pointer">
                        Porcentaje
                    </th>
                    <!-- Otras columnas -->
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($allocations as $allocation)
                    <tr>
                        <td>{{ $allocation->nro_cargo }}</td>
                        <td>{{ number_format($allocation->porc_ipres, 2) }}%</td>
                        <!-- Otras columnas -->
                        <td>
                            <button
                                wire:click="edit({{ $allocation->nro_cargo }})"
                                class="text-blue-600 hover:text-blue-800"
                            >
                                Editar
                            </button>
                            <button
                                wire:click="delete({{ $allocation->nro_cargo }})"
                                class="text-red-600 hover:text-red-800 ml-2"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $allocations->links() }}
    </div>
</div>

@push('scripts')
<script>
    // Notificaciones
    window.addEventListener('notify', event => {
        // Implementa tu sistema de notificaciones preferido
        alert(event.detail.message);
    });
</script>
@endpush

