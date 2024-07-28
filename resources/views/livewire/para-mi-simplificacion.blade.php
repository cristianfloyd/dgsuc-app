<div>
    <div class="container mx-auto mt-10 bg-gray-900 text-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6">Afip Mapuche Mi Simplificación</h2>
        <div class="flex justify-between items-center mb-4">
            <div class="flex flex-col w-1/2">
                <x-mary-input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search') }}" icon="o-magnifying-glass" hint="Buscar..."/>
            </div>
            {{-- <select wire:model.live="perPage" class="form-select ml-2">
                <option value="5">5</option>
                <option value="7">7</option>
                <option value="10">10</option>
                <option>20</option>
                <option>50</option>
                <option>100</option>
            </select> --}}
            <div class="w-5/12 m-4 float-right">
                <x-mary-range
                wire:model.live.debounce="perPage"
                    min="5"
                    max="95"
                    step="5"
                    label="Seleccione cantidad de registros por página: {{$perPage}}"
                    hint="Mayor que 10."
                    class="range-accent float-right" />
            </div>
            <x-mary-toggle label="Right" wire:model="item2" right hint="Marcar Completado" />
            <div>
                <x-mary-button wire:click="exportarTxt" label="Exportar TXT" class="btn-success" />
            </div>
        </div>
        <div class="overflow-x-scroll">
            <table class="w-full">
                <thead>
                    <tr>
                        @foreach ($this->headers as $header)
                            <th class="w-full px-5 py-1 leading-none bg-gray-800 text-left text-xs font-light text-gray-200 tracking-wider">
                                {{ ucfirst(str_replace('_', ' ', $header)) }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-gray-800">
                        @foreach ($dataTable as $rows)
                            <tr>
                                @foreach ($this->headers as $value)
                                    <td class="w-full text-center px-1 py-1 text-xs leading-none">{{ $rows[$value] }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">
        {{ $dataTable->links() }}
    </div>
</div>

