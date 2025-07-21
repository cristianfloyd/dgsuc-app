<x-filament-panels::page>
    <div class="max-w-2xl mx-auto space-y-6">
        {{-- Formulario nativo de Filament --}}
        {{ $this->form }}

        {{-- Acciones del formulario --}}
        <x-filament::actions :actions="$this->getFormActions()" />

        {{-- Visualización del progreso --}}
        @if($tracking)
            <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-6"
                 wire:poll.3s="pollTracking">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Progreso de la copia
                        </h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Estado:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  @class([
                                    'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200' => $tracking->status === 'pending',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' => $tracking->status === 'running',
                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' => $tracking->status === 'completed',
                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' => $tracking->status === 'failed',
                                  ])>
                                {{ ucfirst($tracking->status) }}
                            </span>
                        </div>
                    </div>

                    {{-- Mensaje animado durante la copia --}}
                    @if($tracking->status === 'running')
                        <div class="flex items-center space-x-2 text-blue-600 dark:text-blue-300">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            <span>Procesando copia masiva...</span>
                        </div>
                    @endif

                    {{-- Barra de progreso accesible --}}
                    <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700" role="progressbar"
                         aria-valuenow="{{ $tracking->copied_records }}"
                         aria-valuemin="0"
                         aria-valuemax="{{ $tracking->total_records }}">
                        <div class="bg-primary-600 h-3 rounded-full transition-all duration-500 ease-out"
                             style="width: {{ $tracking->total_records > 0 ? ($tracking->copied_records / $tracking->total_records * 100) : 0 }}%">
                        </div>
                    </div>

                    {{-- Estadísticas --}}
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Copiados</div>
                            <div class="text-lg font-semibold">{{ number_format($tracking->copied_records) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total</div>
                            <div class="text-lg font-semibold">{{ number_format($tracking->total_records) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Porcentaje</div>
                            <div class="text-lg font-semibold">
                                {{ $tracking->total_records > 0 ? number_format($tracking->copied_records / max(1, $tracking->total_records) * 100, 1) : 0 }}%
                            </div>
                        </div>
                    </div>

                    {{-- Mensajes de estado --}}
                    @if($tracking->status === 'failed' && $tracking->error_message)
                        <div class="rounded-md bg-red-50 p-4 dark:bg-red-900/50">
                            <div class="text-sm text-red-800 dark:text-red-200">
                                <strong>Error:</strong> {{ $tracking->error_message }}
                            </div>
                            {{-- Botón para reintentar --}}
                            <button wire:click="startCopy" class="mt-2 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                Reintentar
                            </button>
                        </div>
                    @endif

                    @if($tracking->status === 'completed')
                        <div class="rounded-md bg-green-50 p-4 dark:bg-green-900/50">
                            <div class="text-sm text-green-800 dark:text-green-200">
                                <strong>¡Copia completada exitosamente!</strong>
                                Se copiaron {{ number_format($tracking->copied_records) }} registros.
                            </div>
                        </div>
                    @endif

                    {{-- Fechas de inicio y fin --}}
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        @if($tracking->created_at)
                            <div>Inicio: {{ $tracking->created_at->format('d/m/Y H:i:s') }}</div>
                        @endif
                        @if($tracking->updated_at && $tracking->status === 'completed')
                            <div>Fin: {{ $tracking->updated_at->format('d/m/Y H:i:s') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
