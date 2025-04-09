<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Sección de Ayuda -->
        <div x-show="$wire.isHelpVisible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2"
        class="rounded-xl bg-white shadow-lg ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700 p-2">
            <div class="p-6">
                <div
                     class="mt-4 space-y-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:!text-white">{{ __('Guía de Usuario: Actualización SICOSS') }}</h2>
                    <p class="text-gray-700 dark:text-gray-200">La herramienta de actualización SICOSS permite a los usuarios actualizar y verificar datos necesarios para la generación de archivos SICOSS. Este proceso asegura que los datos de los agentes estén correctamente categorizados.</p>

                    <h3 class="text-lg font-semibold text-gray-900 dark:!text-white">{{ __('Acceso al Sistema') }}</h3>
                    <ul class="list-disc pl-5 text-gray-700 dark:text-gray-200">
                        <li><strong>{{ __('Ruta de Acceso') }}</strong>: /afip-panel/sicoss-updates</li>
                        <li><strong>{{ __('Navegación') }}</strong>: AFIP > Actualización SICOSS</li>
                        <li><strong>{{ __('Permisos') }}</strong>: Necesitas acceso al panel AFIP para utilizar esta herramienta.</li>
                    </ul>

                    <h3 class="text-lg font-semibold text-gray-900 dark:!text-white">{{ __('Proceso de Actualización') }}</h3>
                    <ol class="list-decimal pl-5 text-gray-700 dark:text-gray-200">
                        <li><strong>{{ __('Seleccionar Liquidaciones') }}</strong>: Usa el widget de selección para elegir las liquidaciones que deseas procesar. Filtra por período fiscal si es necesario.</li>
                        <li><strong>{{ __('Ejecutar Actualizaciones') }}</strong>: Haz clic en "Ejecutar Actualizaciones" para iniciar el proceso. El sistema determinará automáticamente si debe usar tablas actuales o históricas.</li>
                        <li><strong>{{ __('Revisar Resultados') }}</strong>: Observa el progreso y los resultados detallados. Verifica si hay agentes sin código de actividad.</li>
                    </ol>

                    <h3 class="text-lg font-semibold text-gray-900 dark:!text-white">{{ __('Resultados y Feedback') }}</h3>
                    <ul class="list-disc pl-5 text-gray-700 dark:text-gray-200">
                        <li><strong>{{ __('Indicador de Progreso') }}</strong>: Muestra el avance del proceso.</li>
                        <li><strong>{{ __('Resultados Detallados') }}</strong>: Incluye un listado de liquidaciones seleccionadas y agentes sin actividad.</li>
                        <li><strong>{{ __('Notificaciones') }}</strong>: Recibirás mensajes de éxito o error al finalizar el proceso.</li>
                    </ul>

                    <h3 class="text-lg font-semibold text-gray-900 dark:!text-white">{{ __('Consideraciones Técnicas') }}</h3>
                    <ul class="list-disc pl-5 text-gray-700 dark:text-gray-200">
                        <li><strong>{{ __('Seguridad') }}</strong>: El acceso está controlado mediante permisos en el panel.</li>
                        <li><strong>{{ __('Performance') }}</strong>: El sistema está optimizado para procesar grandes cantidades de datos de manera eficiente.</li>
                    </ul>

                    <h3 class="text-lg font-semibold text-gray-900 dark:!text-white">{{ __('Soporte') }}</h3>
                    <p class="text-gray-700 dark:text-gray-200">{{ __('Para asistencia adicional, contacta al equipo de soporte técnico ...') }}</p>
                </div>
            </div>
        </div>

        @if(empty($selectedIdLiqui))
            <div class="rounded-xl bg-amber-50 p-4 text-amber-700 dark:bg-amber-900 dark:text-amber-200">
                <p class="font-medium">
                    Por favor, seleccione el periodo fiscal para continuar.
                </p>
            </div>
        @endif

        @if($isProcessing)
            <div class="flex items-center justify-center p-6">
                <x-filament::loading-indicator class="w-8 h-8" />
                <span class="ml-3">Procesando actualizaciones...</span>
            </div>
        @endif

        @if(!empty($selectedIdLiqui) && !$isProcessing && empty($updateResults))
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">
                        Liquidaciones seleccionadas
                    </h3>
                    <div class="mt-4">
                        <ul class="list-disc pl-5 space-y-2">
                            @foreach($selectedIdLiqui as $idLiqui)
                                <li class="text-sm text-gray-700 dark:text-gray-200">
                                    Liquidación #{{ $idLiqui }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if(!empty($updateResults))
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="p-6">
                    <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">
                        Resultados de la actualización
                    </h3>

                    <dl class="mt-4 grid grid-cols-1 gap-4">
                        @foreach($updateResults as $key => $result)
                            @if($key !== 'status' && $key !== 'message')
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ str_replace('_', ' ', ucfirst($key)) }}
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        @if(is_array($result))
                                            Filas afectadas: {{ $result['rows_affected'] ?? 0 }}
                                        @else
                                            {{ $result }}
                                        @endif
                                    </dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>

                    @if(isset($updateResults['status']))
                        <div class="mt-6">
                            <div @class([
                                'rounded-lg p-4',
                                'bg-green-50 text-green-700' => $updateResults['status'] === 'success',
                                'bg-red-50 text-red-700' => $updateResults['status'] === 'error'
                            ])>
                                {{ $updateResults['message'] }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if(isset($updateResults['verificacion_agentes']))
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="p-6">
                        <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">
                            Verificación de Agentes sin Código de Actividad
                        </h3>

                        @if($updateResults['verificacion_agentes']['total_agentes'] > 0)
                            <div class="mt-4">
                                <div class="rounded-lg bg-amber-50 p-4 text-amber-700">
                                    <p class="font-medium">
                                        Se encontraron {{ $updateResults['verificacion_agentes']['total_agentes'] }} agentes sin código de actividad
                                    </p>
                                </div>

                                <div class="mt-4 overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead>
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Legajo
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Situación
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Condición
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($updateResults['verificacion_agentes']['agentes_sin_actividad'] as $agente)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                        {{ $agente->nro_legajo }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                        {{ $agente->codigosituacion }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                        {{ $agente->codigocondicion }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="mt-4">
                                <div class="rounded-lg bg-green-50 p-4 text-green-700">
                                    <p class="font-medium">
                                        No se encontraron agentes sin código de actividad
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>
</x-filament-panels::page>
