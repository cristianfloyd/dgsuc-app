<x-filament-widgets::widget>
    <div
        x-data="{ isCollapsed: @entangle('isCollapsed') }"
        class="rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"
    >
        {{-- Cabecera siempre visible --}}
        <button
            type="button"
            class="w-full flex items-center gap-x-3 px-4 py-2 text-start"
            wire:click="toggleCollapsed"
        >
            <span class="flex-1 font-medium text-gray-800 dark:text-white">
                Totales SICOSS
            </span>
            <div class="flex items-center gap-x-2">
                {{-- Indicador de carga --}}
                <div wire:loading wire:target="updateTotales">
                    <x-filament::loading-indicator class="h-5 w-5" />
                </div>
                @if(!empty($totales))
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Total Aportes: {{ money($totales['total_aportes'] ?? 0, 'ARS') }}
                    </span>
                @endif
            </div>
            <span
                class="transform transition-transform duration-200"
                :class="{ 'rotate-180': !isCollapsed }"
            >
                <x-heroicon-s-chevron-down class="h-5 w-5 text-gray-400" />
            </span>
        </button>

        {{-- Contenido colapsable --}}
        <div
            x-cloak
            x-show="!isCollapsed"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="border-t border-gray-200 dark:border-gray-700"
        >
            <div class="p-4">
                <div
                    class="grid gap-4 md:grid-cols-2 lg:grid-cols-4"
                    style="display: grid;"
                    wire:loading.class="opacity-50"
                    wire:loading.delay
                >
                    @if(empty($totales))
                        <div class="col-span-full flex justify-center items-center p-4">
                            <x-filament::loading-indicator class="h-8 w-8" />
                            <span class="ml-2">Cargando totales...</span>
                        </div>
                    @else
                        @foreach ([
                            'Aportes' => $totales['total_aportes'] ?? 0,
                            'Contribuciones' => $totales['total_contribuciones'] ?? 0,
                            'Remunerativo' => $totales['total_remunerativo'] ?? 0,
                            'No Remunerativo' => $totales['total_no_remunerativo'] ?? 0,
                        ] as $label => $value)
                            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                                <div class="flex items-center gap-x-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Total {{ $label }}
                                </div>
                                <div class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                                    {{ money($value, 'ARS') }}
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
