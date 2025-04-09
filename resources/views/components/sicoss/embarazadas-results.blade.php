@props(['updateResults'])

@if(!empty($updateResults) && isset($updateResults['data']) && isset($updateResults['data']['log_details']))
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">
                Resultados de la actualización de embarazadas
            </h3>
            <div class="mt-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Registros actualizados
                        </dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $updateResults['data']['registros_afectados'] ?? 0 }}
                        </dd>
                    </div>
                    
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                            Tiempo de procesamiento
                        </dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                            {{ number_format(($updateResults['data']['duracion_ms'] ?? 0) / 1000, 2) }} segundos
                        </dd>
                    </div>
                </div>
                
                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">
                        Detalles del proceso
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Parámetro
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Valor
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @if(isset($updateResults['data']['log_details']->parametros))
                                    @php
                                        $parametros = json_decode($updateResults['data']['log_details']->parametros);
                                    @endphp
                                    @foreach($parametros as $key => $value)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ $key }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ is_string($value) ? $value : json_encode($value) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
