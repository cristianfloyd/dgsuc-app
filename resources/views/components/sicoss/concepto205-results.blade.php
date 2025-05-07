@props(['updateResults'])

@if (isset($updateResults['data']) && isset($updateResults['data']['registros_procesados']))
    <div class="rounded-xl bg-white overflow-hidden shadow p-4 dark:bg-gray-800 space-y-4">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Resultados de Actualización Concepto 205</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
                    <tr>
                        <th scope="col" class="px-4 py-3">Detalle</th>
                        <th scope="col" class="px-4 py-3">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <th scope="row" class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                            Registros Procesados
                        </th>
                        <td class="px-4 py-3">
                            {{ $updateResults['data']['registros_procesados'] }}
                        </td>
                    </tr>
                    @if (isset($updateResults['data']['liquidaciones']))
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row" class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                Liquidaciones Procesadas
                            </th>
                            <td class="px-4 py-3">
                                {{ implode(', ', $updateResults['data']['liquidaciones']) }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="text-sm text-gray-500 dark:text-gray-400">
            <p>Esta actualización crea una tabla temporal con datos del concepto 205 para agentes que cumplen requisitos específicos.</p>
            <p class="mt-1">Los montos calculados son el 50% del total del concepto 789.</p>
        </div>
    </div>
@endif
