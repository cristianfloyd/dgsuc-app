<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Sección de Instrucciones --}}
        <div class="bg-gray-600 rounded-lg shadow-sm p-4 border border-gray-200">
            <h3 class="text-lg font-medium text-gray-200">
                Instrucciones para la importación
            </h3>
            <div class="mt-2 text-sm text-gray-200">
                <ul class="list-disc list-inside space-y-1">
                    <li>El archivo debe ser un TXT generado por SICOSS</li>
                    <li>Tamaño máximo permitido: 40MB</li>
                    <li>Formato esperado: texto plano con separadores específicos</li>
                </ul>
            </div>
        </div>

        {{-- Formulario de Importación --}}
        <form wire:submit.prevent="import" class="space-y-4">
            {{ $this->form }}

            {{-- Barra de Progreso --}}
            @if($importProgress > 0)
                <div class="relative pt-1">
                    <div class="flex mb-2 items-center justify-between">
                        <div>
                            <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-blue-600 bg-blue-200">
                                Progreso de importación
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-semibold inline-block text-blue-600">
                                {{ $importProgress }}%
                            </span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                        <div
                            wire:loading.class="animate-pulse"
                            style="width: {{ $importProgress }}%"
                            class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500">
                        </div>
                    </div>
                </div>
            @endif

            {{-- Botones de Acción --}}
            <div class="flex justify-end space-x-4">
                <x-filament::button
                    type="button"
                    color="secondary"
                    wire:click="$refresh"
                    wire:loading.attr="disabled">
                    Limpiar
                </x-filament::button>

                <x-filament::button
                    type="submit"
                    wire:loading.attr="disabled">
                    Iniciar Importación
                </x-filament::button>
            </div>
        </form>

        {{-- Resumen de la última importación --}}
        @if(session()->has('lastImport'))
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200 mt-6">
                <h4 class="text-lg font-medium text-gray-900">
                    Resumen de la última importación
                </h4>
                <div class="mt-2">
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Registros procesados</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ session('lastImport.total') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Fecha de importación</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ session('lastImport.date') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        @endif
    </div>

    {{-- Scripts específicos --}}
    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('import-progress-updated', (data) => {
                    // Lógica adicional de UI si es necesaria
                    console.log('Progreso de importación:', data.progress);
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
