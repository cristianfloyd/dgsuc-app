@if($mostrarResumen && $resultadosControles)
    <div class="space-y-4">
        {{-- Sección Totales --}}
        <x-filament::section>
            <x-filament::grid columns="3">
                <x-filament::grid.column>
                    <h3 class="text-lg font-medium">CUILs Procesados</h3>
                    <p class="mt-2 text-3xl font-semibold">
                        {{ number_format($stats['totales']['cuils_procesados'], 0, ',', '.') }}
                    </p>
                </x-filament::grid.column>

                <x-filament::grid.column>
                    <h3 class="text-lg font-medium">CUILs con Diferencias en Aportes</h3>
                    <p class="mt-2 text-3xl font-semibold text-danger-600">
                        {{ number_format($stats['totales']['cuils_con_diferencias_aportes'], 0, ',', '.') }}
                    </p>
                </x-filament::grid.column>

                <x-filament::grid.column>
                    <h3 class="text-lg font-medium">CUILs con Diferencias en Contribuciones</h3>
                    <p class="mt-2 text-3xl font-semibold text-danger-600">
                        {{ number_format($stats['totales']['cuils_con_diferencias_contribuciones'], 0, ',', '.') }}
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
                        'mt-2 text-3xl font-semibold',
                        'text-danger-600' => $stats['totales_monetarios']['diferencia_aportes'] > 0,
                        'text-success-600' => $stats['totales_monetarios']['diferencia_aportes'] <= 0,
                    ])>
                        $ {{ number_format($stats['totales_monetarios']['diferencia_aportes'], 2, ',', '.') }}
                    </p>
                </x-filament::grid.column>

                <x-filament::grid.column>
                    <h3 class="text-lg font-medium">Diferencia Total en Contribuciones</h3>
                    <p @class([
                        'mt-2 text-3xl font-semibold',
                        'text-danger-600' => $stats['totales_monetarios']['diferencia_contribuciones'] > 0,
                        'text-success-600' => $stats['totales_monetarios']['diferencia_contribuciones'] <= 0,
                    ])>
                        $ {{ number_format($stats['totales_monetarios']['diferencia_contribuciones'], 2, ',', '.') }}
                    </p>
                </x-filament::grid.column>
            </x-filament::grid>
        </x-filament::section>

        {{-- Diferencias por Dependencia --}}
        <x-filament::section>
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

        {{-- Comparación con F.931 --}}
        <x-filament::section>
            <h3 class="text-lg font-medium mb-4">Comparación con F.931</h3>
            <x-filament::grid columns="2">
                <x-filament::grid.column>
                    <h4 class="font-medium mb-2">Aportes</h4>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">DH21:</dt>
                            <dd class="font-medium">$ {{ number_format($stats['comparacion_931']['aportes']['dh21'], 2, ',', '.') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">SICOSS:</dt>
                            <dd class="font-medium">$ {{ number_format($stats['comparacion_931']['aportes']['sicoss'], 2, ',', '.') }}</dd>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <dt class="text-gray-500">Diferencia:</dt>
                            <dd @class([
                                'font-medium',
                                'text-danger-600' => $stats['comparacion_931']['aportes']['dh21'] - $stats['comparacion_931']['aportes']['sicoss'] > 0,
                                'text-success-600' => $stats['comparacion_931']['aportes']['dh21'] - $stats['comparacion_931']['aportes']['sicoss'] <= 0,
                            ])>
                                $ {{ number_format($stats['comparacion_931']['aportes']['dh21'] - $stats['comparacion_931']['aportes']['sicoss'], 2, ',', '.') }}
                            </dd>
                        </div>
                    </dl>
                </x-filament::grid.column>
                <x-filament::grid.column>
                    <h4 class="font-medium mb-2">Contribuciones</h4>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">DH21:</dt>
                            <dd class="font-medium">$ {{ number_format($stats['comparacion_931']['contribuciones']['dh21'], 2, ',', '.') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">SICOSS:</dt>
                            <dd class="font-medium">$ {{ number_format($stats['comparacion_931']['contribuciones']['sicoss'], 2, ',', '.') }}</dd>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <dt class="text-gray-500">Diferencia:</dt>
                            <dd @class([
                                'font-medium',
                                'text-danger-600' => $stats['comparacion_931']['contribuciones']['dh21'] - $stats['comparacion_931']['contribuciones']['sicoss'] > 0,
                                'text-success-600' => $stats['comparacion_931']['contribuciones']['dh21'] - $stats['comparacion_931']['contribuciones']['sicoss'] <= 0,
                            ])>
                                $ {{ number_format($stats['comparacion_931']['contribuciones']['dh21'] - $stats['comparacion_931']['contribuciones']['sicoss'], 2, ',', '.') }}
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
                    <p class="text-3xl font-semibold mt-2">{{ $stats['cuils_no_encontrados']['en_dh21'] }}</p>
                </x-filament::grid.column>
                <x-filament::grid.column>
                    <h4 class="font-medium text-danger-600">En SICOSS pero no en DH21</h4>
                    <p class="text-3xl font-semibold mt-2">{{ $stats['cuils_no_encontrados']['en_sicoss'] }}</p>
                </x-filament::grid.column>
            </x-filament::grid>
        </x-filament::section>
    </div>
@endif
