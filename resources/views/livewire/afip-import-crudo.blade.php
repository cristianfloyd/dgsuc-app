<div>
    @if (session('status'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            <span class="font-medium">{{ session('status') }}</span>

        </div>

    @endif
    <div class="m-4 p-4 text-center bg-white border border-gray-200 rounded-lg shadow sm:p-8 dark:bg-gray-800 dark:border-gray-700">
        <form class="max-w-sm mx-auto" wire:submit="save">
            <label for="archivos" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"></label>
            <select id="archivos" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                wire:model="selectedArchivo">
                <option selected>Seleccionar Archivo</option>
            @foreach ( $listadoArchivos as $archivo)
                <option value="{{$archivo->id }}" wire:model="archivo">
                    {{$archivo->id}} |{{ $archivo->original_name }}
                </option>
            @endforeach
            </select>
            <select name="baseD" id="" wire:model="selectedTable"
                class="mt-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                @foreach ($tablas as $table)
                    <option value="">{{$table }}</option>
                @endforeach
            </select>
            <button class="mt-4 text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                >
                Importar
            </button>
            @if($showButton)
            <button class="mt-4 text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                wire:click="mostrartabla">
                Mostar Tabla
            </button>
            @endif
        </form>
    </div>
    @if($ocultartabla)
        <div class="mt-4">
            <h2 class="text-xl font-bold mb-4">Datos Importados</h2>
            <table class="table-auto w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">Línea Completa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datosImportados as $dato)
                        <tr>
                            {{-- <td class="border px-4 py-2">{{ $dato->id }}</td> --}}
                            <td class="border px-4 py-2">{{ $dato->linea_completa }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
