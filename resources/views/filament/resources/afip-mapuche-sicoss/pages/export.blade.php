<x-filament-panels::page>
    <x-filament::section>
        {{ $this->form }}

        <div class="mt-4 flex justify-end">
            <x-filament::button wire:click="export" color="success">
                Exportar
            </x-filament::button>
        </div>
    </x-filament::section>

    @if($exportProgress > 0)
    <x-filament::section>
        <div class="space-y-4">
            <h3 class="text-lg font-medium">Progreso de la exportación</h3>

            <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                <div class="bg-primary-600 h-4 rounded-full" style="width: {{ $exportProgress }}%"></div>
            </div>

            <div class="flex justify-between text-sm text-gray-500">
                <span>{{ $processedRecords }} de {{ $totalRecords }} registros</span>
                <span>{{ $exportProgress }}%</span>
            </div>
        </div>
    </x-filament::section>
    @endif

    <x-filament::section>
        <div class="prose max-w-none dark:prose-invert">
            <h3>Información sobre la exportación SICOSS</h3>

            <p>
                La exportación de datos en formato SICOSS permite generar un archivo compatible con el sistema de AFIP para la declaración de empleados y sus remuneraciones.
            </p>

            <h4>Formatos disponibles:</h4>

            <ul>
                <li>
                    <strong>Archivo TXT (SICOSS)</strong>: Formato de texto plano compatible con el sistema SICOSS de AFIP. Este archivo puede ser importado directamente en el aplicativo.
                </li>
                <li>
                    <strong>Archivo Excel</strong>: Formato Excel para análisis y revisión de los datos antes de su presentación. Este formato no es compatible con el aplicativo SICOSS.
                </li>
            </ul>

            <h4>Opciones adicionales:</h4>

            <ul>
                <li>
                    <strong>Incluir Inactivos</strong>: Si se activa, se incluirán en la exportación los empleados que figuran como inactivos en el período seleccionado.
                </li>
            </ul>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 dark:bg-yellow-900/50 dark:border-yellow-600">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 dark:text-yellow-200">
                            Asegúrese de verificar los datos antes de presentarlos en AFIP. La información exportada debe coincidir con los registros oficiales de nómina.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>
