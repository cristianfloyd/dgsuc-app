@php use App\Enum\WorkflowStatus; @endphp
<div>
    <!-- Sección de alertas -->
    <div class="fixed top-0 right-0 m-4">
        @foreach ($this->messages as $message)
            <div class="alert alert-{{ $message['type'] }}">
                {{ $message['message'] }}
            </div>
        @endforeach
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
                            @foreach (WorkflowStatus::cases() as $step)
                                <li class="flex items-center">
                                    <span class="mr-2">{{ $step->value }}:</span>
                                    @if ($this->workflowService->isStepCompleted($this->processLog, $step->value))
                                        <span class="text-green-500">Completado</span>
                                    @elseif ($step->value === $this->currentStep)
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
                        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Cuils Mapuche que no estan en Afip
                        </h5>
                        @if ($this->showLoadButton)
                            <x-mary-button wire:click="loadCuilsNotInAfip" class="btn-outline">
                                Load
                            </x-mary-button>
                        @endif

                        @if ($this->stepsCompleted)
                            <x-mary-button wire:click="showParaMiSimplificacionAndCuilsNoEncontrados"
                                class="btn-outline">
                                Mostrar MiSimplificacion y CUILs sin datos
                            </x-mary-button>
                            <x-mary-button class="btn-outline mt-2" wire:click="startNewProcess">
                                Iniciar Nuevo Flujo de Trabajo
                            </x-mary-button>
                        @endif

                        @if ($ShowMiSimplificacion)
                            <div class="mt-2 mb-2">
                                <x-mary-button wire:click="updateShowMiSimplificacion" class="btn-outline">
                                    Crear tabla temporal
                                </x-mary-button>
                                <p class="font-normal text-gray-700 dark:text-gray-400">
                                    Cantidad de cuils que no estan en Afip: {{ $cuilsCount }}
                                </p>
                            </div>
                        @endif

                        @if ($this->showCuilsNoInsertedButton)
                            <x-mary-button wire:click="showLoadCuilsNoEncontrados" class="btn-outline">
                                Cuils no Encontrados
                            </x-mary-button>
                        @endif

                        @if ($this->showExecuteStoredFunctionButton)
                            <x-mary-button wire:click="funcionAlmacenada" class="btn-outline rounded">
                                Ejecutar Funcion Almacenada
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
</div>
