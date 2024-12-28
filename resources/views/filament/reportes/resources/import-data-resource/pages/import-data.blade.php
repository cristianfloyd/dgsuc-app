<x-filament-panels::page>
    <div class="space-y-6">
        <div class="p-6 bg-gray-700 rounded-lg shadow">
            <h2 class="text-lg font-medium mb-4">Importar datos desde Excel</h2>

            <form wire:submit="import">
                {{ $this->form }}

                <div class="mt-4">
                    <x-filament::button type="submit">
                        Importar archivo
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
