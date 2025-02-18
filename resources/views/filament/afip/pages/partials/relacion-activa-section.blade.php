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
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Puesto</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200">
                {{ $relacionActiva->puesto_desem }}
            </dd>
        </div>
    </dl>
</div>
@endif
