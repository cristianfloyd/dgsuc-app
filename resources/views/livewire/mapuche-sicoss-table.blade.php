<div>
    <div class="flex flex-col">
        <div class="mb-4">
            <input wire:model.live.debounce.300ms="{{__('search')}}" type="text" placeholder="Buscar..." class="form-input">
            <select wire:model.live="perPage" class="form-select ml-2">
                <option value="5">5</option>
                <option value="7">7</option>
                <option value="10">10</option>
                <option>20</option>
                <option>50</option>
                <option>100</option>
            </select>
        </div>
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                @foreach ($columns as $column)
                                    <th scope="col"
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $column }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        {{-- <tbody>
                            @foreach ($dataArray as $row)
                                <tr wire:key="{{ $row['_key'] }}">
                                    @foreach ($columns as $column)
                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-500">
                                            {{  }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody> --}}
                        <tbody class=" divide-y bg-gray-900 divide-gray-200">
                            @foreach ($dataArray as $row)
                                <tr>
                                    @foreach ($columns as $column)
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-200">{{ $row[$column] ?? '' }}</div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-4">
            {{ $data->links() }}
        </div>
    </div>
</div>
