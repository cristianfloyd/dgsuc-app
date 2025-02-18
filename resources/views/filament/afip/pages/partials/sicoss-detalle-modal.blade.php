<div class="space-y-6">
    {{-- Sección de Diferencias --}}
    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
        <h3 class="text-lg font-medium">Diferencias Detectadas</h3>
        <dl class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">CUIL</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">{{ $record->cuil }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Diferencia</dt>
                <dd @class([
                    'mt-1 text-sm font-medium',
                    'text-danger-600 dark:text-danger-400' => $record->diferencia > 0,
                    'text-success-600 dark:text-success-400' => $record->diferencia <= 0,
                ])>
                    $ {{ number_format($record->diferencia, 2, ',', '.') }}
                </dd>
            </div>
        </dl>
    </div>

    {{-- Sección de Cálculo SICOSS --}}
    @if($sicossCalculo)
    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
        <h3 class="text-lg font-medium">Cálculo SICOSS</h3>
        <dl class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Remuneración Total</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    $ {{ number_format($sicossCalculo->remtotal, 2, ',', '.') }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Aportes SIJP</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    $ {{ number_format($sicossCalculo->aportesijp, 2, ',', '.') }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Aportes INSSJP</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    $ {{ number_format($sicossCalculo->aporteinssjp, 2, ',', '.') }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contribución SIJP</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    $ {{ number_format($sicossCalculo->contribucionsijp, 2, ',', '.') }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contribución INSSJP</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    $ {{ number_format($sicossCalculo->contribucioninssjp, 2, ',', '.') }}
                </dd>
            </div>
        </dl>
    </div>
    @endif

    {{-- Sección de Relación Activa --}}
    @if($relacionActiva)
    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
        <h3 class="text-lg font-medium">Relación Laboral</h3>
        <dl class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Modalidad Contrato</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ $relacionActiva->modalidad_contrato }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha Inicio</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ \Carbon\Carbon::parse($relacionActiva->fecha_inicio_relacion_laboral)->format('d/m/Y') }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Retribución Pactada</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    $ {{ number_format($relacionActiva->retribucion_pactada, 2, ',', '.') }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Modalidad Liquidación</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ $relacionActiva->modalidad_liquidacion }}
                </dd>
            </div>
        </dl>
    </div>
    @endif
</div>
