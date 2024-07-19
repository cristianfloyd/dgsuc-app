
<div>
    <div class="container max-w-screen-md mx-auto p-4">
        @if($crearTablaTemp)
            <div class="inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" id="cargo-modal">
                <livewire:tabla-temp-cuils /><!-- Agrega el componente Livewire aquí -->
                @if($tableTempCreated)
                    <div>
                        <p>La tabla temporal ya está creada.</p>
                        <button wire:click="dropTableTemp" class="px-4 py-2 bg-blue-500 text-white rounded">
                            Eliminar Tabla Temporal
                        </button>
                    </div>
                @endif
            </div>
        @endif
        <div class="float-left m-4">
            <a href="#"
                class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Cuils Mapuche que no
                    estan en Afip</h5>
                <button wire:click="loadCuilsNotInAfip"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Load
                </button>
                @if ($cuilsNotInAfipLoaded)
                    <button wire:click="showCuilsDetails" class="px-4 py-2 bg-blue-500 text-white rounded">
                        Crear tabla temporal
                    </button>
                @endif
                @if($tableTempCreated)
                    <button wire:click="$this->dropTableTemp()" class="px-4 py-2 bg-blue-500 text-white rounded">
                        Drop Table Temp
                    </button>
                @endif  
                @if ($cuilsNotInAfipLoaded)
                    <p class="font-normal text-gray-700 dark:text-gray-400">
                        Cantidad de cuils que no estan en Afip: {{ $cuilsNotInAfip->count() }}
                    </p>
                @endif
            </a>
        </div>

        @if ($cuilsNotInAfipLoaded)
            <div class="overflow-x-auto content-center">
                <table class="max-w-xs bg-gray">
                    <thead>
                        <tr>
                            <th
                                class="items-center py-2 px-4 bg-gray-800 text-gray-200 font-semibold text-sm uppercase">
                                CUIL
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->compareCuils as $cuil)
                            <tr class="border-b">
                                <td class="py-2 px-4" wire:click="searchCuil('{{ $cuil }}')">
                                    <button>
                                        {{ $cuil }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $this->compareCuils->links() }}
                </div>

            </div>
        @endif

        @if ($showDetails)
            <livewire:para-mi-simplificacion
                :nroLiqui="$nroLiqui"
                :periodoFiscal="$periodoFiscal"
                :cuils="$cuilstosearch"
                />
        @endif
    </div>


    @if ($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" id="my-modal">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Información del Empleado</h3>
                    <div class="mt-2 px-7 py-3">
                        @if ($employeeInfo)
                            <p class="text-sm text-gray-500">
                                <strong>Nombre:</strong> {{ $employeeInfo['nombre'] }}
                                {{ $employeeInfo['apellido'] }}<br>
                                <button wire:click="showCargos('{{ $employeeInfo['nro_legaj'] }}')"
                                    class="text-blue-600 hover:underline">
                                    Legajo: {{ $employeeInfo['nro_legaj'] }}
                                </button><br>
                                {{-- <strong>Legajo:</strong> {{ $employeeInfo['nro_legaj'] }}<br> --}}
                                <strong>DNI:</strong> {{ $employeeInfo['DNI'] }}<br>
                                <strong>Fecha de inicio:</strong> {{ $employeeInfo['fecha_inicio'] }}
                            </p>
                        @else
                            <p class="text-sm text-gray-500">No se encontró información para el DNI {{ $selectedDni }}
                            </p>
                        @endif
                    </div>
                    <div class="items-center px-4 py-3">
                        <button wire:click="closeModal"
                            class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if ($showCargoModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" id="cargo-modal">
            <div class="relative top-20 mx-auto p-5 border w-2/4 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Cargos del Empleado</h3>
                    <div class="mt-2 px-7 py-3">
                        @if (count($cargos) > 0)
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2">Cargo</th>
                                        <th class="px-4 py-2">Categ</th>
                                        <th class="px-4 py-2">Fecha Alta</th>
                                        <th class="px-4 py-2">Fecha Baja</th>
                                        <th class="px-4 py-2">Vigencia</th>
                                        <th class="px-4 py-2">Vig mes</th>
                                        <th class="px-4 py-2">Stop liq</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cargos as $cargo)
                                        <tr>
                                            {{-- 'cod_cargo','codc_categ','fec_alta', 'fec_baja','vig_caano', 'vig_cames' --}}
                                            <td class="border px-4 py-2">{{ $cargo['nro_cargo'] }}</td>
                                            <td class="border px-4 py-2">{{ $cargo['codc_categ'] }}</td>
                                            <td class="border px-4 py-2">{{ $cargo['fec_alta'] }}</td>
                                            <td class="border px-4 py-2">{{ $cargo['fec_baja'] ?? 'Activo' }}</td>
                                            <td class="border px-4 py-2">{{ $cargo['vig_caano'] }}</td>
                                            <td class="border px-4 py-2">{{ $cargo['vig_cames'] }}</td>
                                            <td class="border px-4 py-2">{{ $cargo['chkstopliq'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-sm text-gray-500">No se encontraron cargos para este empleado.</p>
                        @endif
                    </div>
                    <div class="items-center px-4 py-3">
                        <button wire:click="closeCargoModal"
                            class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
