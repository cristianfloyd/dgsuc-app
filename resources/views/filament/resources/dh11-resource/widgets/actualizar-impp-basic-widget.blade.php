<x-filament-widgets::widget>
    <x-filament::card>
        <form wire:submit.prevent="previsualizar">
            {{ $this->form }}
        </form>

        @if ($previewData->isNotEmpty())
            <div class="mt-4">
                <h3 class="text-lg font-medium">Vista previa de cambios</h3>
                <div class="mt-2 overflow-x-auto">
                    @if ($showConfirmButton)
                        <div class="mt-4">
                            <x-filament::button wire:click="confirmarCambios"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Confirmar y aplicar cambios
                            </x-filament::button>
                            <x-filament::button wire:click="cancelarCambios"
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Cancelar cambios
                            </x-filament::button>
                        </div>
                    @endif
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th
                                    class="px-6 py-3 bg-gray-900 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Código Categoría</th>
                                <th
                                    class="px-6 py-3 bg-gray-900 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Importe Básico Actual</th>
                                <th
                                    class="px-6 py-3 bg-gray-900 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Importe Básico Nuevo</th>
                            </tr>
                        </thead>
                        <tbody class="bg-gray-900 divide-y divide-gray-200">
                            @foreach ($previewData as $item)
                                <tr>
                                    <td class="px-6 py-4 bg-gray-900 text-sm text-gray-200">{{ $item['codc_categ'] }}
                                    </td>
                                    <td class="px-6 py-4 bg-gray-900 text-sm text-gray-200">
                                        {{ number_format($item['impp_basic_actual'], 2) }}</td>
                                    <td class="px-6 py-4 bg-gray-900 text-sm text-gray-200">
                                        {{ number_format($item['impp_basic_nuevo'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </x-filament::card>
</x-filament-widgets::widget>
