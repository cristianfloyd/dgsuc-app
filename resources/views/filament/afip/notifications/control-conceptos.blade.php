<div class="space-y-2">
    <div class="text-sm text-gray-600 dark:text-gray-400">
        Período: <span class="font-medium">{{ $year }}-{{ sprintf('%02d', $month) }}</span>
    </div>

    <div class="overflow-hidden bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                Resultados del Control de Conceptos
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                Resumen de importes por concepto
            </p>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-700 dark:text-blue-300">Total Aportes</h4>
                    <p class="text-lg font-bold text-blue-800 dark:text-blue-200">
                        $ {{ number_format($totalAportes, 2, ',', '.') }}
                    </p>
                </div>

                <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg">
                    <h4 class="text-sm font-medium text-green-700 dark:text-green-300">Total Contribuciones</h4>
                    <p class="text-lg font-bold text-green-800 dark:text-green-200">
                        $ {{ number_format($totalContribuciones, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Se han guardado {{ count($resultados) }} conceptos para el período {{ $year }}-{{ sprintf('%02d', $month) }}.
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Haga clic en "Ver Detalles" para ver el desglose completo.
            </p>
        </div>
    </div>
</div>
