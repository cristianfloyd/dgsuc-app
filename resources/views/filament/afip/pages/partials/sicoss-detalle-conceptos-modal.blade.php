@php
    $tipoConce = $record->dh12?->tipo_conce;
@endphp
<div class="space-y-6">
    {{-- Información del Concepto --}}
    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
        <h3 class="text-lg font-medium">Datos del Concepto</h3>
        <dl class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Código</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ $record->codn_conce }}
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Descripción</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ $record->desc_conce }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Importe</dt>
                <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-200">
                    $ {{ number_format($record->importe, 2, ',', '.') }}
                </dd>
            </div>
        </dl>
    </div>

    {{-- Información del Período --}}
    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
        <h3 class="text-lg font-medium">Información del Período</h3>
        <dl class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Año</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ $record->year }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mes</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ sprintf('%02d', $record->month) }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Fecha de Control</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ \Carbon\Carbon::parse($record->created_at)->format('d/m/Y H:i:s') }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Conexión</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ $record->connection_name }}
                </dd>
            </div>
        </dl>
    </div>

    {{-- Fórmulas del Concepto --}}
    @if($record->dh12 && $record->dh12->dh13s && $record->dh12->dh13s->count() > 0)
    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
        <h3 class="text-lg font-medium">Fórmulas de Cálculo</h3>
        <div class="mt-2 space-y-4">
            @foreach($record->dh12->dh13s->sortBy('nro_orden_formula') as $formula)
            <div class="border-l-4 border-blue-500 pl-4">
                <div class="text-sm">
                    <div class="font-medium text-gray-700 dark:text-gray-300">
                        Orden #{{ $formula->nro_orden_formula }}
                    </div>
                    @if($formula->desc_calcu)
                    <div class="mt-1">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Cálculo:</span>
                        <span class="text-gray-900 dark:text-gray-200">{{ $formula->desc_calcu }}</span>
                    </div>
                    @endif
                    @if($formula->desc_condi)
                    <div class="mt-1">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Condición:</span>
                        <span class="text-gray-900 dark:text-gray-200">{{ $formula->desc_condi }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Acumuladores del Concepto --}}
    @if($record->dh12 && $record->dh12->dh14s && $record->dh12->dh14s->count() > 0)
    <div class="rounded-xl bg-gray-50 p-4 mt-4 dark:bg-gray-800">
        <h3 class="text-lg font-medium">Acumuladores</h3>
        <div class="mt-2 space-y-4">
            @foreach($record->dh12->dh14s as $acumulador)
            <div class="border-l-4 border-green-500 pl-4">
                <div class="text-sm">
                    <div class="font-medium text-gray-700 dark:text-gray-300">
                        Acumulador #{{ $acumulador->nro_acumu }}
                    </div>
                    @if($acumulador->desc_acumu)
                    <div class="mt-1">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Descripción:</span>
                        <span class="text-gray-900 dark:text-gray-200">{{ $acumulador->desc_acumu }}</span>
                    </div>
                    @endif
                    <div class="mt-1">
                        <span class="font-medium text-gray-500 dark:text-gray-400">Tipo:</span>
                        <span class="text-gray-900 dark:text-gray-200">{{ $acumulador->tipo_acumu->getDescripcion() }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Información de Clasificación y Detalles --}}
    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
        <h3 class="text-lg font-medium">Clasificación y Detalles del Concepto</h3>
        <dl class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo SICOSS</dt>
                <dd class="mt-1">
                    <span @class([
                        'px-2 py-1 rounded-full text-xs font-medium',
                        'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300' => $record->es_aporte,
                        'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300' => $record->es_contribucion,
                        'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-300' => !$record->es_aporte && !$record->es_contribucion,
                    ])>
                        {{ $record->tipo_concepto }}
                    </span>
                </dd>
            </div>

            @if($record->dh12)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo de Concepto</dt>
                    <dd class="mt-1">
                        @if($tipoConce)
                            <span @class([
                                'px-2 py-1 rounded-full text-xs font-medium',
                                'bg-success-100 text-success-800 dark:bg-success-900/20 dark:text-success-300' => $tipoConce->getBadgeColor() === 'success',
                                'bg-warning-100 text-warning-800 dark:bg-warning-900/20 dark:text-warning-300' => $tipoConce->getBadgeColor() === 'warning',
                                'bg-info-100 text-info-800 dark:bg-info-900/20 dark:text-info-300' => $tipoConce->getBadgeColor() === 'info',
                                'bg-danger-100 text-danger-800 dark:bg-danger-900/20 dark:text-danger-300' => $tipoConce->getBadgeColor() === 'danger',
                                'bg-primary-100 text-primary-800 dark:bg-primary-900/20 dark:text-primary-300' => $tipoConce->getBadgeColor() === 'primary',
                                'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-300' => $tipoConce->getBadgeColor() === 'gray',
                            ])>
                                {{ $tipoConce->getDescription() }}
                            </span>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Ejemplo: {{ $tipoConce->getExample() }}
                            </p>
                        @else
                            <span class="text-sm text-gray-500 dark:text-gray-400">No especificado</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipo de Distribución</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                        {{ $record->dh12->tipo_distr?->value ?? 'No especificado' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Descripción Corta</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                        {{ $record->dh12->desc_corta ?? 'No especificado' }}
                    </dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Características</dt>
                    <dd class="mt-1 text-sm">
                        <div class="flex flex-wrap gap-2">
                            @if($record->dh12->flag_acumu)
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-300">
                                    Acumulativo
                                </span>
                            @endif
                            @if($record->dh12->chk_acumsac)
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300">
                                    Acumula SAC
                                </span>
                            @endif
                            @if($record->dh12->sino_legaj)
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                                    Por Legajo
                                </span>
                            @endif
                            @if($record->dh12->sino_visible)
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300">
                                    Visible
                                </span>
                            @endif
                            @if($tipoConce?->isRemunerativo())
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-300">
                                    Remunerativo
                                </span>
                            @endif
                            @if($tipoConce?->tieneAportes())
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-800 dark:bg-rose-900/20 dark:text-rose-300">
                                    Con Aportes
                                </span>
                            @endif
                        </div>
                    </dd>
                </div>
            @else
                <div class="col-span-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400 italic">
                        No se encontró información adicional del concepto en DH12
                    </p>
                </div>
            @endif
        </dl>
    </div>
</div>
