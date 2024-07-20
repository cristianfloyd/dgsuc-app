<div class="container">
    <div class="row justify-content-center">
        <div class="mt-2">
            <ul>
                <li>
                    <!-- Archivo select menu... -->
                    <select
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                            focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700
                            dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        wire:model.live="selectedArchivo">
                        <option selected>Seleccionar Archivo</option>
                        @foreach ($listadoArchivos as $archivo)
                            <option wire:key="{{ $archivo->id }}" value="{{ $archivo->id }}">
                                Nombre :{{ $archivo->original_name }}
                            </option>
                        @endforeach
                    </select>
                    {{-- wire:model.live="selectedCity" wire:key="{{ $selectedState }}" --}}
                    <x-label wire:model.live="archivoName" wire:key="{{ $archivoName }}">
                        Archivo seleccionado: {{ $archivoName }}
                    </x-label>
                </li>
                <h3> </h3>
                <button type="button"
                    class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                    wire:click="seleccionarArchivo">
                    Seleccionar archivo
                </button>
                <button type="button"
                    class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700"
                    wire:click="importarTabla" wire:confirm="Estas Seguro?">
                    XX
                </button>
            </ul>
        </div>
        <div>
            <div class="mt-2">
                <div class="relative">
                    <input type="text" id="disabled_outlined"
                        class="block px-2.5 pb-2.5 pt-4 w-full text-sm text-gray-900 bg-transparent rounded-lg border-1 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                        placeholder=" " disabled />
                    <label for="disabled_outlined"
                        class="absolute text-sm text-gray-400 dark:text-gray-500 duration-300 transform -translate-y-4 scale-75 top-2 z-10 origin-[0] bg-white dark:bg-gray-900 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-2 peer-focus:scale-75 peer-focus:-translate-y-4 start-1 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto">{{ $cantRegistros }}</label>
                </div>
            </div>

        </div>
    </div>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div>
            <x-button wire:click="contarLineas">
                Contarlineas
            </x-button>
        </div>

    </div>
</div>
