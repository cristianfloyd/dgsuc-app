<div class="p-6 bg-white rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6">Gestión de Cargos y Asociaciones</h2>

    <!-- Mensaje de alerta -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-900 rounded-md">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 text-red-900 rounded-md">
            {{ session('error') }}
        </div>
    @endif

    <!-- Formulario de creación/edición -->
    <div class="mb-8 p-4 border rounded-md bg-gray-50">
        <h3 class="text-lg font-semibold mb-4">{{ $editId ? 'Editar' : 'Crear' }} Registro</h3>

        <form wire:submit.prevent="save" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="nro_cargo" class="block text-sm font-medium text-gray-700">Número de Cargo</label>
                    <input type="number" id="nro_cargo" wire:model="form.nro_cargo"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                        {{ $editId ? 'readonly' : '' }}>
                    @error('form.nro_cargo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="nro_cargoasociado" class="block text-sm font-medium text-gray-700">Número de Cargo Asociado</label>
                    <input type="number" id="nro_cargoasociado" wire:model="form.nro_cargoasociado"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('form.nro_cargoasociado') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="tipoasociacion" class="block text-sm font-medium text-gray-700">Tipo de Asociación</label>
                    <input type="text" id="tipoasociacion" wire:model="form.tipoasociacion" maxlength="1"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('form.tipoasociacion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                @if ($editId)
                    <button type="button" wire:click="resetForm" class="px-4 py-2 bg-gray-200 rounded-md">
                        Cancelar
                    </button>
                @endif

                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">
                    {{ $editId ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </form>
    </div>

    <!-- Filtros -->
    <div class="mb-6 p-4 border rounded-md bg-gray-50">
        <h3 class="text-lg font-semibold mb-3">Filtros</h3>

        <div class="flex flex-wrap gap-3">
            <div class="w-full md:w-auto">
                <label for="filtroTipo" class="block text-sm font-medium text-gray-700">Tipo de Asociación</label>
                <select id="filtroTipo" wire:model.live="filtroTipo"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Todos</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>

            <button wire:click="limpiarFiltros" class="mt-auto px-3 py-2 bg-gray-200 rounded-md">
                Limpiar Filtros
            </button>

            <button wire:click="mostrarConAsociacion" class="mt-auto px-3 py-2 bg-blue-500 text-white rounded-md">
                Ver Solo Con Asociaciones
            </button>

            <button wire:click="crearRelacion" class="mt-auto px-3 py-2 bg-green-500 text-white rounded-md">
                Crear Relación
            </button>
        </div>
    </div>

    <!-- Tabla de resultados -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border rounded-lg">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="py-3 px-4 border-b text-left">Nro. Cargo</th>
                    <th class="py-3 px-4 border-b text-left">Nro. Cargo Asociado</th>
                    <th class="py-3 px-4 border-b text-left">Tipo Asociación</th>
                    <th class="py-3 px-4 border-b text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($registros as $registro)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 border-b">{{ $registro->nro_cargo }}</td>
                        <td class="py-3 px-4 border-b">{{ $registro->nro_cargoasociado ?? '-' }}</td>
                        <td class="py-3 px-4 border-b">{{ trim($registro->tipoasociacion) ?? '-' }}</td>
                        <td class="py-3 px-4 border-b text-center">
                            <div class="flex justify-center space-x-2">
                                <button wire:click="edit({{ $registro->nro_cargo }})"
                                    class="px-2 py-1 bg-yellow-500 text-white rounded">
                                    Editar
                                </button>
                                <button wire:click="confirmarEliminacion({{ $registro->nro_cargo }})"
                                    class="px-2 py-1 bg-red-500 text-white rounded">
                                    Eliminar
                                </button>
                                <button wire:click="$dispatch('buscar-relaciones', { nroCargo: {{ $registro->nro_cargo }} })"
                                    class="px-2 py-1 bg-blue-500 text-white rounded">
                                    Ver Relaciones
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-gray-500">
                            No se encontraron registros
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $registros->links() }}
    </div>

    <!-- Modal de confirmación de eliminación -->
    @if ($mostrarConfirmacion)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                <h3 class="text-lg font-bold mb-4">Confirmar eliminación</h3>
                <p class="mb-6">¿Estás seguro de que deseas eliminar este registro? Esta acción no se puede deshacer.</p>

                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelarEliminacion" class="px-4 py-2 bg-gray-200 rounded-md">
                        Cancelar
                    </button>
                    <button wire:click="delete" class="px-4 py-2 bg-red-600 text-white rounded-md">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal para mostrar relaciones -->
    <div id="modalRelaciones" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-2xl w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold" id="tituloRelaciones">Relaciones del Cargo</h3>
                <button onclick="document.getElementById('modalRelaciones').classList.add('hidden')"
                    class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div id="contenidoRelaciones" class="overflow-x-auto">
                <!-- Se llenará dinámicamente -->
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        // Manejo de eventos para mostrar relaciones
        Livewire.on('mostrar-relaciones', ({ relaciones, nroCargo }) => {
            const modal = document.getElementById('modalRelaciones');
            const titulo = document.getElementById('tituloRelaciones');
            const contenido = document.getElementById('contenidoRelaciones');

            titulo.textContent = `Relaciones del Cargo #${nroCargo}`;

            if (relaciones.length === 0) {
                contenido.innerHTML = '<p class="text-center py-4">No se encontraron relaciones para este cargo.</p>';
            } else {
                let tabla = `
                    <table class="min-w-full bg-white border rounded-lg">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700">
                                <th class="py-2 px-3 border-b text-left">Nro. Cargo</th>
                                <th class="py-2 px-3 border-b text-left">Nro. Cargo Asociado</th>
                                <th class="py-2 px-3 border-b text-left">Tipo Asociación</th>
                                <th class="py-2 px-3 border-b text-left">Relación</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                relaciones.forEach(rel => {
                    let relacion = '';
                    if (rel.nro_cargo == nroCargo) {
                        relacion = 'Principal';
                    } else if (rel.nro_cargoasociado == nroCargo) {
                        relacion = 'Asociado';
                    }

                    tabla += `
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-3 border-b">${rel.nro_cargo}</td>
                            <td class="py-2 px-3 border-b">${rel.nro_cargoasociado || '-'}</td>
                            <td class="py-2 px-3 border-b">${rel.tipoasociacion ? rel.tipoasociacion.trim() : '-'}</td>
                            <td class="py-2 px-3 border-b">${relacion}</td>
                        </tr>
                    `;
                });

                tabla += `
                        </tbody>
                    </table>
                `;

                contenido.innerHTML = tabla;
            }

            modal.classList.remove('hidden');
        });

        // Actualizar listado después de crear/editar/eliminar
        Livewire.on('registro-guardado', () => {
            // Puedes agregar código adicional si es necesario
        });

        Livewire.on('registro-eliminado', () => {
            // Puedes agregar código adicional si es necesario
        });

        Livewire.on('relacion-creada', () => {
            // Puedes agregar código adicional si es necesario
        });

        // Actualizar listado filtrado
        Livewire.on('actualizar-listado', ({ registros }) => {
            // Este evento se maneja internamente en Livewire
        });
    });
</script>
