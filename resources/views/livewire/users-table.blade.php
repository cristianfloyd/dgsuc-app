<div>
    <section class="mt-10">
        <div class="mx-auto max-w-screen-2xl px-4 lg:px-12">
            <!-- Start coding here -->
            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                <div class="flex items-center justify-between d p-4">
                    <div class="flex">

                        {{-- <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                    fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text" id="default-search"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500
                            block w-full  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500
                            dark:focus:border-blue-500 pl-10 p-6 pb-2 pt-2"
                                placeholder="{{ __('Search') }} {{ __('Name') }} , {{ __('Email') }}"
                                required="">
                        </div> --}}

                        <form class="max-w-xl mx-auto">
                            <label for="default-search"
                                class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">{{ __('Search') }}</label>

                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                    </svg>
                                </div>
                                <input wire:model.live.debounce.200ms="search" wire:offline.class="disabled" type="search" id="default-search"
                                    class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                    placeholder="{{ __('Buscar Legajo, Nombre, Apellido...') }}" required />
                            </div>

                        </form>
                    </div>
                    <div class="flex space-x-3">
                        @include('livewire.includes.user-types')

                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-sm text-gray-300 uppercase bg-gray-700">
                            <tr>
                                @include('livewire.includes.table-sort-th', [
                                    'name' => 'nro_legaj',
                                    'displayName' => 'Legajo',
                                ])
                                @include('livewire.includes.table-sort-th', [
                                    'name' => 'desc_appat',
                                    'displayName' => 'Apellido',
                                ])
                                @include('livewire.includes.table-sort-th', [
                                    'name' => 'desc_nombr',
                                    'displayName' => 'Nombre',
                                ])
                                @include('livewire.includes.table-sort-th', [
                                    'name' => 'nro_cuil',
                                    'displayName' => 'DNI',
                                ])
                                @include('livewire.includes.table-sort-th', [
                                    'name' => 'periodoalta',
                                    'displayName' => 'Last Updated',
                                ])
                                @include('livewire.includes.table-sort-th', [
                                    'name' => 'tipo_estad',
                                    'displayName' => 'Estad',
                                ])
                                <th scope="col" class="px-4 py-3"> <span class="sr-only">{{ __('Actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr wire:key="{{ $user->id }}" class="border-b dark:border-gray-700">
                                    <th scope="row"
                                        class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $user->nro_legaj }}</th>
                                    <td class="px-4 py-3">{{ $user->desc_appat }}</td>
                                    <td class="px-4 py-3">{{ $user->desc_nombr }}</td>
                                    <td class="px-4 py-3">{{ $user->nro_cuil }}</td>
                                    <td class="px-4 py-3">{{ $user->periodoalta }}</td>
                                    <td class="px-4 py-3 text-{{ $user->tipo_estad ? 'green-500' : 'blue-500' }}">{{ $user->tipo_estad ? 'Admin' : __('Member') }}</td>
                                    <td class="px-4 py-3">
                                    <button
                                        onclick="confirm('Seguro quieres borrar el usuario: {{ $user->name }}?') || event.stopImmediatePropagation()"
                                        wire:click="deleteUser({{ $user->id }})"
                                        class="px-3 py-1 bg-red-500 text-white rounded">X</button>
                                    </td>
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
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
