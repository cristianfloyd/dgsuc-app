<div class="space-y-2">
    <div class="text-sm text-gray-600 dark:text-gray-400">
        Período: <span class="font-medium">{{ $year }}-{{ sprintf('%02d', $month) }}</span>
    </div>

    <div class="overflow-hidden bg-white dark:bg-gray-800 shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                Resultados del Control de Conteos
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                Cantidad de registros en cada tabla
            </p>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700">
            <dl>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                        Tabla DH21 Aportes
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:col-span-2 sm:mt-0 font-bold">
                        {{ number_format($conteos['dh21aporte'], 0, ',', '.') }} registros
                    </dd>
                </div>

                <div class="bg-white dark:bg-gray-800 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                        Tabla SICOSS Cálculos
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:col-span-2 sm:mt-0 font-bold">
                        {{ number_format($conteos['sicoss_calculos'], 0, ',', '.') }} registros
                    </dd>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                        Tabla SICOSS
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:col-span-2 sm:mt-0 font-bold">
                        {{ number_format($conteos['sicoss'], 0, ',', '.') }} registros
                    </dd>
                </div>

                <div class="bg-white dark:bg-gray-800 px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">
                        Diferencia DH21 vs SICOSS Cálculos
                    </dt>
                    <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0 font-bold {{ abs($conteos['dh21aporte'] - $conteos['sicoss_calculos']) > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                        {{ number_format(abs($conteos['dh21aporte'] - $conteos['sicoss_calculos']), 0, ',', '.') }} registros
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="text-xs text-gray-500 dark:text-gray-400 italic mt-2">
        Nota: Las diferencias en los conteos pueden indicar problemas en la sincronización de datos entre sistemas.
    </div>
</div>
