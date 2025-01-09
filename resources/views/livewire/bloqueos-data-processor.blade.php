<div>
    @if($isProcessing)
        <div class="space-y-4">
            <div class="text-sm text-gray-600">
                Procesando {{ $registrosProcesados }} de {{ $totalRegistros }} registros
            </div>

            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full"
                     style="width: {{ $porcentajeCompletado }}%">
                </div>
            </div>

            <div class="text-right text-sm text-gray-600">
                {{ number_format($porcentajeCompletado, 1) }}%
            </div>
        </div>
    @endif
    <div class="space-y-4">
        {{-- Panel de Estadísticas --}}
        <div class="grid grid-cols-4 gap-4">
            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold">{{ $estadisticas['total'] }}</div>
                    <div class="text-sm text-gray-600">Total Procesados</div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-success-600">
                        {{ $estadisticas['exitosos'] }}
                    </div>
                    <div class="text-sm text-gray-600">Exitosos</div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-danger-600">
                        {{ $estadisticas['fallidos'] }}
                    </div>
                    <div class="text-sm text-gray-600">Fallidos</div>
                </div>
            </x-filament::card>
        </div>

        {{-- Tabla de Resultados --}}
        @if ($resultados && $resultados->count() > 0)
            <x-filament-tables::table>
                <x-slot name="header">
                    <x-filament-tables::header-cell>Cargo        </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Legajo       </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Tipo         </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Estado       </x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Fecha Proceso</x-filament-tables::header-cell>
                    <x-filament-tables::header-cell>Resultado    </x-filament-tables::header-cell>
                </x-slot>

                @foreach ($resultados as $resultado)
                    @php
                        $resource = $resultado;
                    @endphp
                    <x-filament-tables::row>
                        <x-filament-tables::cell>{{ $resource['cargo'] }}</x-filament-tables::cell>
                        <x-filament-tables::cell>{{ $resource['legajo'] }}</x-filament-tables::cell>
                        <x-filament-tables::cell>{{ $resource['tipo_bloqueo'] }}</x-filament-tables::cell>
                        <x-filament-tables::cell>
                            <x-filament::badge :color="$resource['estado'] === 'Procesado' ? 'success' : 'danger'">
                                {{ $resource['estado'] }}
                            </x-filament::badge>
                        </x-filament-tables::cell>
                        <x-filament-tables::cell>{{ $resource['fecha_proceso'] }}</x-filament-tables::cell>
                        <x-filament-tables::cell>{{ $resource['resultado'] }}</x-filament-tables::cell>
                    </x-filament-tables::row>
                @endforeach
            </x-filament-tables::table>
        @endif
    </div>
</div>
