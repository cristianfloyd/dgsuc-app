@props(['verificacionAgentes'])

@if(isset($verificacionAgentes))
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">
                Verificación de Agentes sin Código de Actividad
            </h3>

            @if($verificacionAgentes['total_agentes'] > 0)
                <div class="mt-4">
                    <div class="rounded-lg bg-amber-50 p-4 text-amber-700">
                        <p class="font-medium">
                            Se encontraron {{ $verificacionAgentes['total_agentes'] }} agentes sin código de actividad
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
                                @foreach($verificacionAgentes['agentes_sin_actividad'] as $agente)
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
