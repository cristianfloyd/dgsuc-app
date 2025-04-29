<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-gray-700 rounded-lg shadow">
            <h2 class="text-lg font-medium mb-4">Importar datos desde Excel</h2>

            <form wire:submit="import">
                {{ $this->form }}

                <div class="mt-6 flex justify-end">
                    <x-filament::button wire:loading.attr="disabled" wire:target="import" type="submit">
                        <span wire:loading.remove wire:target="import">
                            Importar archivo
                        </span>
                        <span wire:loading wire:target="import">
                            <svg class="animate-spin h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                            Procesando...
                        </span>
                    </x-filament::button>
                </div>
            </form>
        </div>

        <div class="p-4 bg-gray-800 rounded-lg">
            <h3 class="text-sm font-medium mb-2">Formato esperado del archivo Excel:</h3>
            <ul class="list-disc list-inside text-sm text-gray-500">
                <li>email</li>
                <li>nombre</li>
                <li>usuario_mapuche</li>
                <li>dependencia</li>
                <li>nro_legaj</li>
                <li>nro_cargo</li>
                <li>fecha_baja</li>
                <li>tipo</li>
                <li>observaciones (opcional)</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>
