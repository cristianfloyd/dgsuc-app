<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Sección de Ayuda -->
        <x-sicoss.help-section />

        @if(empty($selectedIdLiqui))
            <div
                class="rounded-xl bg-amber-50 p-4 text-amber-700 dark:bg-amber-900 dark:text-amber-200"
            >
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
            <x-sicoss.selected-liquidaciones :liquidaciones="$selectedIdLiqui" :liquidacionDefinitiva="$selectedliquiDefinitiva" />
        @endif

        @if (!empty($updateResults))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 6000)"
                x-show="show"
                x-transition
                role="alert"
                @class([
                    'relative',
                    'rounded-lg p-4 my-4 border',
                    'bg-green-50 border-green-300 text-green-800' => ($updateResults['status'] ?? '') === 'success',
                    'bg-red-50 border-red-300 text-red-800' => ($updateResults['status'] ?? '') === 'error',
                    'bg-yellow-50 border-yellow-300 text-yellow-800' => ($updateResults['status'] ?? '') === 'warning',
                    'dark:bg-green-900 dark:text-green-100 dark:border-green-700' => ($updateResults['status'] ?? '') === 'success',
                    'dark:bg-red-900 dark:text-red-100 dark:border-red-700' => ($updateResults['status'] ?? '') === 'error',
                    'dark:bg-yellow-900 dark:text-yellow-100 dark:border-yellow-700' => ($updateResults['status'] ?? '') === 'warning',
                ])
            >
                <button
                    type="button"
                    @click="show = false"
                    class="absolute top-2 right-2 text-xl text-gray-400 hover:text-gray-700 focus:outline-none"
                    aria-label="Cerrar"
                >
                    &times;
                </button>

                <div class="font-bold mb-1">
                    @if (($updateResults['status'] ?? '') === 'success')
                        ✅ Éxito
                    @elseif (($updateResults['status'] ?? '') === 'error')
                        ❌ Error
                    @elseif (($updateResults['status'] ?? '') === 'warning')
                        ⚠️ Advertencia
                    @endif
                </div>
                <div>
                    {{ $updateResults['message'] ?? 'Sin mensaje.' }}
                </div>
                @if (!empty($updateResults['details']))
                    <div class="mt-2 text-sm text-gray-700">
                        <pre>{{ print_r($updateResults['details'], true) }}</pre>
                    </div>
                @endif
            </div>
        @endif

        <!-- Resultados de actualización de embarazadas -->
        <x-sicoss.embarazadas-results :updateResults="$updateResults" />

        <!-- Resultados de actualización concepto 205 -->
        <x-sicoss.concepto205-results :updateResults="$updateResults" />

        <!-- Resultados generales -->
        <x-sicoss.general-results :updateResults="$updateResults" />

        <!-- Verificación de agentes -->
        @if(isset($updateResults['verificacion_agentes']))
            <x-sicoss.agentes-verification :verificacionAgentes="$updateResults['verificacion_agentes']" />
        @endif
    </div>
</x-filament-panels::page>
