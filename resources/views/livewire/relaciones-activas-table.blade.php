<div>
    <section class="mt-5">
        <div class="mx-auto max-w-screen-xl px-4 lg:px-12">
            {{-- <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8"> --}}
                <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                    <div class="flex items-center justify-between d p-4">
                        <div class="basis-1/3">

                            <form class="mx-auto">
                                <label for="default-search"
                                    class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">{{ __('Search') }}</label>

                                <div class="relative">
                                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                        </svg>
                                    </div>
                                    <input wire:model.live.debounce.200ms="search" wire:offline.class="disabled"
                                        type="search" id="default-search"
                                        class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                        placeholder="{{ __('Buscar CUIL') }}" required />
                                </div>
                            </form>

                        </div>
                        <div class="space-x-3">
                            {{-- @include('livewire.includes.user-types') --}}

                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                            <thead class="text-sm text-gray-300 uppercase bg-gray-700">
                                <tr>
                                    {{-- @include('livewire.includes.table-sort-th', [
                                        'name' => 'id',
                                        'displayName' => 'id',
                                    ]) --}}
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'periodo_fiscal',
                                        'displayName' => 'Periodo Fiscal',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'codigo_movimiento',
                                        'displayName' => 'c mov',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'tipo_registro',
                                        'displayName' => 'T reg',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'cuil',
                                        'displayName' => 'cuil',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'marca_trabajador_agropecuario',
                                        'displayName' => 't agro',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'modalidad_contrato',
                                        'displayName' => 'mod cont',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'fecha_inicio_relacion_laboral',
                                        'displayName' => 'f inicio',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'fecha_fin_relacion_laboral',
                                        'displayName' => 'f fin',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'codigo_o_social',
                                        'displayName' => 'c o social',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'cod_situacion_baja',
                                        'displayName' => 'c s baja',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'fecha_telegrama_renuncia',
                                        'displayName' => 'f telegrama',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'retribucion_pactada',
                                        'displayName' => 'retribucion',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'modalidad_liquidacion',
                                        'displayName' => 'mod liqui',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'suc_domicilio_desem',
                                        'displayName' => 'suc dom',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'actividad_domicilio_desem',
                                        'displayName' => 'act dom',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'puesto_desem',
                                        'displayName' => 'puesto',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'rectificacion',
                                        'displayName' => 'rect',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'numero_formulario_agro',
                                        'displayName' => 'n form agro',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'tipo_servicio',
                                        'displayName' => 't serv',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'categoria_profesional',
                                        'displayName' => 'cat prof',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'ccct',
                                        'displayName' => 'ccct',
                                    ])
                                    @include('livewire.includes.table-sort-th', [
                                        'name' => 'no_hay_datos',
                                        'displayName' => 'no hay datos',
                                    ])
                                    <th scope="col" class="px-4 py-3">
                                        <span class="sr-only">{{ __('Actions') }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($relacionesActivas as $relacionActiva)
                                    <tr wire:key="{{ $relacionActiva->id }}" class="border-b dark:border-gray 700">
                                        {{-- <th scope="row"
                                            class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ $relacionActiva->id }}</th>
                                        </th> --}}
                                        <td class="px-4 py-3">{{ $relacionActiva->periodo_fiscal }}</td>
                                        <td class="px-4 py-3">{{ $relacionActiva->codigo_movimiento }}</td>
                                        <td class="px-4 py-3">{{ $relacionActiva->tipo_registro }}</td>
                                        {{-- <td class="px-4 py-3">{{ $relacionActiva->cuil }}</td> --}}
                                        <th scope="row"
                                            class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                        >{{ $relacionActiva->cuil }}</th>
                                        <td class="px-4 py-3">{{ $relacionActiva->marca_trabajador_agropecuario }}</td>
                                        <td class="px-4 py-3">{{ $relacionActiva->modalidad_contrato }}</td>
                                        <td class="px-4 py-3">{{ $relacionActiva->fecha_inicio_relacion_laboral }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->fecha_fin_relacion_laboral }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->codigo_o_social }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->cod_situacion_baja }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->fecha_telegrama_renuncia }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->retribucion_pactada }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->modalidad_liquidacion }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->suc_domicilio_desem }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->actividad_domicilio_desem }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->puesto_desem }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->rectificacion }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->numero_formulario_agro }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->tipo_servicio }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->categoria_profesional }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->ccct }} </td>
                                        <td class="px-4 py-3">{{ $relacionActiva->no_hay_datos }} </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="py-4 px-3">
                        <div class="flex ">
                            <div class="flex space-x-6 items-center mb-3 ml-10">
                                <label class="w-32 text-sm font-medium text-gray-400">{{ __('Per Page') }}</label>
                                <select wire:model.live="perPage"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500
            block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
            dark:focus:border-blue-500">
                                    <option value="5">5</option>
                                    <option value="7">7</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            <div class="pl-10 mx-10 items-center mb-3">
                                {{ $relacionesActivas->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            {{-- </div> --}}
        </div>
    </section>
</div>
