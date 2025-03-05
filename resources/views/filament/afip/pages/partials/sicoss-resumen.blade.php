<div
    x-data="{
        loading: false,
        hasData: {{ isset($stats['totales']) ? 'true' : 'false' }},
        async loadResumen() {
            if (this.loading) return;

            this.loading = true;
            try {
                await $wire.cargarResumen();
                this.hasData = true;
            } catch (error) {
                console.error('Error cargando resumen:', error);
            } finally {
                this.loading = false;
            }
        }
    }"
    class="relative"
>
    @if(!isset($stats['totales']))
        <x-filament::section>
            <div class="text-center py-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    Resumen de Controles SICOSS
                </h3>
                <p class="text-gray-500 mb-6">
                    Obtenga un resumen detallado de los controles SICOSS ejecutados.
                </p>
                <x-filament::button
                    wire:loading.attr="disabled"
                    x-on:click="loadResumen"
                    x-bind:disabled="loading"
                >
                    <span x-show="!loading">Ver Resumen</span>
                    <span x-show="loading" x-cloak>
                        <x-filament::loading-indicator class="h-5 w-5" />
                        Cargando...
                    </span>
                </x-filament::button>
            </div>
        </x-filament::section>
    @else
        {{-- Contenido existente del resumen --}}
        <div>
            {{-- NUEVA SECCIÓN: Tarjeta de Control de Conteos (Collapsible) --}}
            @if(isset($conteos))
                <x-filament::section class="mb-6">
                    <div
                        x-data="{ expanded: false }"
                        class="w-full"
                    >
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-2 cursor-pointer" x-on:click="expanded = !expanded">
                                <h3 class="text-lg font-medium">Control de Conteos ({{ $year }}-{{ sprintf('%02d', $month) }})</h3>
                                <button type="button" class="text-gray-500 hover:text-primary-500 transition-colors">
                                    <x-heroicon-o-chevron-down x-show="!expanded" class="w-5 h-5" />
                                    <x-heroicon-o-chevron-up x-show="expanded" class="w-5 h-5" />
                                </button>
                            </div>
                            <x-filament::button
                                size="sm"
                                color="gray"
                                icon="heroicon-o-arrow-path"
                                wire:click="ejecutarControlConteos"
                                wire:loading.attr="disabled"
                            >
                                Actualizar
                            </x-filament::button>
                        </div>

                        <div x-show="expanded" x-collapse x-cloak>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                                    <h4 class="text-sm font-medium text-blue-700 dark:text-blue-300">
                                        Registros en DH21
                                    </h4>
                                    <p class="text-2xl font-bold text-blue-800 dark:text-blue-200">
                                        {{ number_format($conteos['dh21aporte'], 0, ',', '.') }}
                                    </p>
                                </div>

                                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                                    <h4 class="text-sm font-medium text-green-700 dark:text-green-300">
                                        Registros en SICOSS Cálculos
                                    </h4>
                                    <p class="text-2xl font-bold text-green-800 dark:text-green-200">
                                        {{ number_format($conteos['sicoss_calculos'], 0, ',', '.') }}
                                    </p>
                                </div>

                                <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
                                    <h4 class="text-sm font-medium text-purple-700 dark:text-purple-300">
                                        Registros en SICOSS
                                    </h4>
                                    <p class="text-2xl font-bold text-purple-800 dark:text-purple-200">
                                        {{ number_format($conteos['sicoss'], 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            @if($diferencia_conteos > 0)
                                <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                    <h4 class="text-sm font-medium text-red-700 dark:text-red-300">
                                        Diferencia entre DH21 y SICOSS Cálculos
                                    </h4>
                                    <p class="text-xl font-bold text-red-800 dark:text-red-200">
                                        {{ number_format($diferencia_conteos, 0, ',', '.') }} registros
                                    </p>
                                    <p class="text-sm text-red-600 dark:text-red-400 mt-1">
                                        Esta diferencia puede indicar problemas en la sincronización de datos entre sistemas.
                                    </p>
                                </div>
                            @else
                                <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                    <h4 class="text-sm font-medium text-green-700 dark:text-green-300">
                                        No hay diferencias entre DH21 y SICOSS Cálculos
                                    </h4>
                                    <p class="text-sm text-green-600 dark:text-green-400 mt-1">
                                        Los sistemas están correctamente sincronizados.
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- Resumen compacto (visible cuando está colapsado) --}}
                        <div x-show="!expanded" class="mt-2">
                            <div class="flex flex-wrap gap-4 items-center">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center justify-center w-3 h-3 rounded-full bg-blue-500"></span>
                                    <span class="text-sm">DH21: <strong>{{ number_format($conteos['dh21aporte'], 0, ',', '.') }}</strong></span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center justify-center w-3 h-3 rounded-full bg-green-500"></span>
                                    <span class="text-sm">SICOSS Cálculos: <strong>{{ number_format($conteos['sicoss_calculos'], 0, ',', '.') }}</strong></span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center justify-center w-3 h-3 rounded-full bg-purple-500"></span>
                                    <span class="text-sm">SICOSS: <strong>{{ number_format($conteos['sicoss'], 0, ',', '.') }}</strong></span>
                                </div>

                                @if($diferencia_conteos > 0)
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center justify-center w-3 h-3 rounded-full bg-red-500"></span>
                                        <span class="text-sm text-red-600 font-medium">Diferencia: <strong>{{ number_format($diferencia_conteos, 0, ',', '.') }}</strong></span>
                                    </div>
                                @else
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center justify-center w-3 h-3 rounded-full bg-green-500"></span>
                                        <span class="text-sm text-green-600 font-medium">Sin diferencias</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-filament::section>
            @else
                <x-filament::section class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Control de Conteos</h3>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            No hay datos de conteos disponibles. Ejecute el control de conteos para ver los resultados.
                        </p>
                        <x-filament::button
                            color="primary"
                            icon="heroicon-o-list-bullet"
                            wire:click="ejecutarControlConteos"
                            wire:loading.attr="disabled"
                        >
                            Ejecutar Control de Conteos
                        </x-filament::button>
                    </div>
                </x-filament::section>
            @endif

            <x-filament::grid
                style="--cols-lg: repeat(4, minmax(0, 1fr));"
                class="lg:grid-cols-[--cols-lg] py-4"
            >
                {{-- Sección Totales --}}
                <x-filament::section>
                    <x-filament::grid columns="3">
                        <x-filament::grid.column>
                            <h3 class="text-lg font-medium">CUILs Procesados</h3>
                            <p class="mt-2 text-2xl font-semibold">
                                {{ number_format($stats['totales']['cuils_procesados'] ?? 0, 0, ',', '.') }}
                            </p>
                        </x-filament::grid.column>

                        <x-filament::grid.column>
                            <h3 class="text-lg font-medium">CUILs con Diferencias en Aportes</h3>
                            <p class="mt-2 text-2xl font-semibold text-danger-600">
                                {{ number_format($stats['totales']['cuils_con_diferencias_aportes'] ?? 0, 0, ',', '.') }}
                            </p>
                        </x-filament::grid.column>

                        <x-filament::grid.column>
                            <h3 class="text-lg font-medium">CUILs con Diferencias en Contribuciones</h3>
                            <p class="mt-2 text-2xl font-semibold text-danger-600">
                                {{ number_format($stats['totales']['cuils_con_diferencias_contribuciones'] ?? 0, 0, ',', '.') }}
                            </p>
                        </x-filament::grid.column>
                    </x-filament::grid>
                </x-filament::section>

                {{-- Diferencias Monetarias Totales --}}
                <x-filament::section>
                    <x-filament::grid columns="2">
                        <x-filament::grid.column>
                            <h3 class="text-lg font-medium">Diferencia Total en Aportes</h3>
                            <p @class([
                                'mt-2 text-2xl font-semibold',
                                'text-danger-600' => $stats['totales_monetarios']['diferencia_aportes'] > 0,
                                'text-success-600' => $stats['totales_monetarios']['diferencia_aportes'] <= 0,
                            ])>
                                $ {{ number_format($stats['totales_monetarios']['diferencia_aportes'] ?? 0, 2, ',', '.') }}
                            </p>
                        </x-filament::grid.column>

                        <x-filament::grid.column>
                            <h3 class="text-lg font-medium">Diferencia Total en Contribuciones</h3>
                            <p @class([
                                'mt-2 text-2xl font-semibold',
                                'text-danger-600' => $stats['totales_monetarios']['diferencia_contribuciones'] > 0,
                                'text-success-600' => $stats['totales_monetarios']['diferencia_contribuciones'] <= 0,
                            ])>
                                $ {{ number_format($stats['totales_monetarios']['diferencia_contribuciones'] ?? 0, 2, ',', '.') }}
                            </p>
                        </x-filament::grid.column>
                    </x-filament::grid>
                </x-filament::section>

                {{-- Comparación con F.931 --}}
                <x-filament::section>
                    <h3 class="text-lg font-medium mb-4">Comparación con F.931</h3>
                    <x-filament::grid columns="2">
                        <x-filament::grid.column>
                            <h4 class="font-medium mb-2">Aportes</h4>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">DH21:</dt>
                                    <dd class="font-medium">$ {{ number_format($stats['comparacion_931']['aportes']['dh21'] ?? 0, 2, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">SICOSS:</dt>
                                    <dd class="font-medium">$ {{ number_format($stats['comparacion_931']['aportes']['sicoss'] ?? 0, 2, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between border-t pt-2">
                                    <dt class="text-gray-500">Diferencia:</dt>
                                    <dd @class([
                                        'font-medium',
                                        'text-danger-600' => $stats['comparacion_931']['aportes']['dh21'] - $stats['comparacion_931']['aportes']['sicoss'] > 0,
                                        'text-success-600' => $stats['comparacion_931']['aportes']['dh21'] - $stats['comparacion_931']['aportes']['sicoss'] <= 0,
                                    ])>
                                        $ {{ number_format($stats['comparacion_931']['aportes']['dh21'] - $stats['comparacion_931']['aportes']['sicoss'] ?? 0, 2, ',', '.') }}
                                    </dd>
                                </div>
                            </dl>
                        </x-filament::grid.column>
                        <x-filament::grid.column>
                            <h4 class="font-medium mb-2">Contribuciones</h4>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">DH21:</dt>
                                    <dd class="font-medium">$ {{ number_format($stats['comparacion_931']['contribuciones']['dh21'] ?? 0, 2, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">SICOSS:</dt>
                                    <dd class="font-medium">$ {{ number_format($stats['comparacion_931']['contribuciones']['sicoss'] ?? 0, 2, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between border-t pt-2">
                                    <dt class="text-gray-500">Diferencia:</dt>
                                    <dd @class([
                                        'font-medium',
                                        'text-danger-600' => $stats['comparacion_931']['contribuciones']['dh21'] - $stats['comparacion_931']['contribuciones']['sicoss'] > 0,
                                        'text-success-600' => $stats['comparacion_931']['contribuciones']['dh21'] - $stats['comparacion_931']['contribuciones']['sicoss'] <= 0,
                                    ])>
                                        $ {{ number_format($stats['comparacion_931']['contribuciones']['dh21'] - $stats['comparacion_931']['contribuciones']['sicoss'] ?? 0, 2, ',', '.') }}
                                    </dd>
                                </div>
                            </dl>
                        </x-filament::grid.column>
                    </x-filament::grid>
                </x-filament::section>

                {{-- CUILs No Encontrados --}}
                <x-filament::section>
                    <h3 class="text-lg font-medium mb-4">CUILs No Encontrados</h3>
                    <x-filament::grid columns="2">
                        <x-filament::grid.column>
                            <h4 class="font-medium text-danger-600">En DH21 pero no en SICOSS</h4>
                            <p class="text-2xl font-semibold mt-2">{{ $stats['cuils_no_encontrados']['en_dh21'] ?? 0 }}</p>
                        </x-filament::grid.column>
                        <x-filament::grid.column>
                            <h4 class="font-medium text-danger-600">En SICOSS pero no en DH21</h4>
                            <p class="text-2xl font-semibold mt-2">{{ $stats['cuils_no_encontrados']['en_sicoss'] ?? 0 }}</p>
                        </x-filament::grid.column>
                    </x-filament::grid>
                </x-filament::section>
            </x-filament::grid>
            {{-- Diferencias por Dependencia --}}
            <x-filament::section class="col-span-1">
                <h3 class="text-lg font-medium mb-4">Diferencias por Dependencia</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2">Dependencia</th>
                                <th class="text-left py-2">Carácter</th>
                                <th class="text-right py-2">Diferencia Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stats['diferencias_por_dependencia'] as $diferencia)
                            <tr class="border-b">
                                <td class="py-2">{{ $diferencia->codc_uacad }}</td>
                                <td class="py-2">{{ $diferencia->caracter }}</td>
                                <td @class([
                                    'py-2 text-right font-medium',
                                    'text-danger-600' => $diferencia->diferencia_total > 0,
                                    'text-success-600' => $diferencia->diferencia_total <= 0,
                                ])>
                                    $ {{ number_format($diferencia->diferencia_total, 2, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
    @endif
</div>
