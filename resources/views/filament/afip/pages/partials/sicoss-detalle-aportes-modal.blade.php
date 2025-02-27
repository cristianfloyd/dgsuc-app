<div class="space-y-6">
    {{-- Información del Agente --}}
    {{-- Información del Agente --}}
    @if($dh01)
    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
        <h3 class="text-lg font-medium">Datos del Agente</h3>
        <dl class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Apellido y Nombre</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ $dh01->desc_appat }}, {{ $dh01->desc_nombr }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Legajo</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ $dh01->nro_legaj }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">CUIL</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ $dh01->cuil_completo }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Documento</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ $dh01->tipo_docum }} {{ $dh01->nro_docum }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estado</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ $dh01->tipo_estad }}
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Codigo Actividad</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                    {{ $record->mapucheSicoss->cod_act }}
                </dd>
            </div>
        </dl>
    </div>
    @else
    <div class="rounded-xl bg-warning-50 p-4 dark:bg-warning-800/20">
        <div class="flex items-center gap-x-3">
            <x-filament::icon
                icon="heroicon-m-exclamation-triangle"
                class="h-6 w-6 text-warning-500 dark:text-warning-400"
            />
            <h3 class="text-sm font-medium text-warning-600 dark:text-warning-400">
                No se encontraron datos del agente
            </h3>
        </div>
    </div>
    @endif


    {{-- Resto de las secciones (SICOSS y Relación Activa) --}}
    {{-- @include('filament.afip.pages.partials.sicoss-calculo-section', ['sicossCalculo' => $sicossCalculo]) --}}
    {{-- @include('filament.afip.pages.partials.relacion-activa-section', ['relacionActiva' => $relacionActiva]) --}}
</div>
