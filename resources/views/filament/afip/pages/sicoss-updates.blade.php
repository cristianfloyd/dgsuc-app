<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Sección de Ayuda -->
        <x-sicoss.help-section />

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
            <x-sicoss.selected-liquidaciones :liquidaciones="$selectedIdLiqui" :liquidacionDefinitiva="$selectedliquiDefinitiva" />
        @endif

        <!-- Resultados de actualización de embarazadas -->
        <x-sicoss.embarazadas-results :updateResults="$updateResults" />

        <!-- Resultados generales -->
        <x-sicoss.general-results :updateResults="$updateResults" />

        <!-- Verificación de agentes -->
        @if(isset($updateResults['verificacion_agentes']))
            <x-sicoss.agentes-verification :verificacionAgentes="$updateResults['verificacion_agentes']" />
        @endif
    </div>
</x-filament-panels::page>
