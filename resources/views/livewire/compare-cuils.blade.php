<div>
    <!-- Sección de alertas -->
    <div class="fixed top-0 right-0 m-4">
        @if ($successMessage)
            <x-alert-success>
                {{ __($successMessage) }}
            </x-alert-success>
        @endif
    </div>

    <!-- Contenedor principal -->
    <div class="container mx-auto w-2/3">
        @if ($crearTablaTemp)
            <div class="inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" id="cargo-modal">
                <livewire:tabla-temp-cuils /><!-- Agrega el componente Livewire aquí -->
            </div>
        @endif

        <div class="flex space-x-4" id="contenedor-secundario">
            <!-- Sección de botones -->
            <div class="w-1/3 grid grid-cols-1" id="workflow-container">
                @if ($latestWorkflow = $this->workflowService->getLatestWorkflow())
                    <div class="mt-8 w-full">
                        <h3 class="text-lg font-semibold mb-2">Progreso del Flujo de Trabajo</h3>
                        <ul class="space-y-2">
                            @foreach ($this->workflowService->getSteps() as $step => $description)
                                <li class="flex items-center">
                                    <span class="mr-2">{{ $description }}:</span>
                                    @if ($this->workflowService->isStepCompleted($latestWorkflow, $step))
                                        <span class="text-green-500">Completado</span>
                                    @elseif ($step === 'current_step')
                                        <span class="text-blue-500">En progreso</span>
                                    @else
                                        <span class="text-gray-500">Pendiente</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="w-1/3 grid grid-cols-1 gap-4" id="buttons-container">
                <div>
                    <div
                        class="block max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700">
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Cuils Mapuche
                            que no
                            estan en Afip</h5>
                        @if ($this->showLoadButton)
                            <x-mary-button wire:click="loadCuilsNotInAfip" class="btn-outline">
                                Load
                            </x-mary-button>
                        @endif
                        @if ($this->showCreateTempTableButton)
                            <div class="mt-2 mb-2">
                                <x-mary-button wire:click="showCuilsDetails" class="btn-outline">
                                    Crear tabla temporal
                                </x-mary-button>
                                <p class="font-normal text-gray-700 dark:text-gray-400">
                                    Cantidad de cuils que no estan en Afip: {{ $cuilsCount }}
                                </p>
                            </div>
                        @endif
                        @if ($this->showExecuteStoredFunctionButton)
                            <x-mary-button wire:click="mapucheMiSimplificacion" class="btn-outline rounded">
                                Generar tabla AfipMapucheMiSimplificacion
                            </x-mary-button>
                        @endif


                        @if ($tableTempCreated)
                            <x-mary-button wire:click="dropTableTemp" class="btn-outline rounded">
                                Drop Table Temp
                            </x-mary-button>
                        @endif
                        @if ($insertTablaTemp)
                            <x-mary-button wire:click="insertTableTemp" class="btn-outline rounded">
                                Insertar en Table Temp
                            </x-mary-button>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Contenedor de dos columnas -->
            <div class="w-1/3" id="TablaCuils">
                @if ($showCuilsTable)
                    <div class="w-1/3">
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
                    </div>
                    <!-- Tabla principal de CUILs -->
                @elseif ($showCuilsNoEncontrados)
                    <div class="w-1/3">
                        <table class="max-w-xs bg-gray">
                            <thead>
                                <tr>
                                    <th
                                        class="items-center py-2 px-4 bg-gray-800 text-gray-200 font-semibold text-sm uppercase">
                                        CUIL Sin Datos
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cuilsNoInserted as $cuil)
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
                    </div>
                @endif
            </div>
        </div>
        @if ($showCuilsTable)
            <div class="w-xl mx-auto mt-4">
                {{ $this->compareCuils->links() }}
            </div>
        @endif
    </div>


    <div class="mt-8 w-full">
        @if ($ShowMiSimplificacion)
            <livewire:para-mi-simplificacion />
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
