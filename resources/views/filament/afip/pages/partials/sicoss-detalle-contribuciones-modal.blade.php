<div class="space-y-6">
    {{-- Similar al anterior pero con campos específicos de contribuciones --}}
    @if($dh01)
    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
        <h3 class="text-lg font-medium">Datos del Agente</h3>
        <dl class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ $dh01->nombre_completo }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Legajo</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ $dh01->nro_legaj }}
                </dd>
            </div>
        </dl>
    </div>
    @endif

    {{-- Diferencias en Contribuciones --}}
    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
        <h3 class="text-lg font-medium">Diferencias en Contribuciones</h3>
        <dl class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contribución SIJP DH21</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    $ {{ number_format($record->contribucionsijpdh21, 2, ',', '.') }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Contribución INSSJP DH21</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    $ {{ number_format($record->contribucioninssjpdh21, 2, ',', '.') }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Diferencia Total</dt>
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

    {{-- Resto de las secciones (SICOSS y Relación Activa) --}}
    @include('filament.afip.pages.partials.sicoss-calculo-section', ['sicossCalculo' => $sicossCalculo])
    @include('filament.afip.pages.partials.relacion-activa-section', ['relacionActiva' => $relacionActiva])
</div>
