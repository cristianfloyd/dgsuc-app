<div class="p-5 mx-auto max-w-2xl">
    <div wire:offLine class="mx-auto max-w-xl">
        <div class="flex items-center p-4 mb-4 text-sm text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50 dark:bg-gray-900 dark:text-yellow-300 dark:border-yellow-800"
            role="alert">
            <svg class="flex-shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                fill="currentColor" viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
            </svg>
            <span class="sr-only">Info</span>
            <div class="mx-auto max-w-xl">
                <span class="font-medium">Warning alert!</span> No internet Conection.
            </div>
        </div>
    </div>

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
                placeholder="{{ __('Buscar Nombres, emails...') }}" required />
        </div>
        
    </form>

    <div class="max-w-xl relative mx-auto mt-4">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">{{ __('name') }}</th>
                    <th scope="col" class="px-6 py-3">{{ __('email') }}</th>
                    <th scope="col" class="px-6 py-3">{{ __('View') }}</th>
                    {{-- <th scope="col" class="px-6 py-3">created_at</th> --}}
                </tr>
            </thead>
            <tbody class="text-xs divide-y divide-gray-200 dark:divide-gray-700 dark:text-gray-400">
                @foreach ($users as $user)
                    <tr wire:key="{{ $user->id }}" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <th scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $user->name }}</th>
                        <td class="px-6 py-4">{{ $user->email }}</td>
                        <td>
                            <button wire:click="viewUser({{ $user }})"
                                class="middle none center rounded-lg bg-teal-500 py-3 px-6 font-sans text-xs font-bold uppercase text-white shadow-md shadow-pink-500/20 transition-all hover:shadow-lg hover:shadow-pink-500/40 focus:opacity-[0.85] focus:shadow-none active:opacity-[0.85] active:shadow-none disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none"
                                data-ripple-light="true">
                                {{ __('View') }}
                            </button>
                        </td>
                        {{-- <td class="px-6 py-4">{{ $user->created_at }}</td> --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div>
            {{ $users->links() }}
        </div>
        @if ($selectedUser)
            <x-livewire.modal name="user-details" title="{{ $selectedUser->name }}">
                @slot('content')
                    <div>
                        <div class="flex justify-center">
                            @isset($selectedUser->image)
                                <img src="{{ asset('storage/' . $selectedUser->image) }}" alt="{{ $selectedUser->name }}"
                                    class="m-2 w-32 h-32 rounded-full">
                            @endisset
                        </div>
                        <div class="flex justify-center">
                            <p class="p-5">{{ $selectedUser->email }}</p>
                        </div>
                    </div>
                @endslot
            </x-livewire.modal>
        @endif
    </div>
</div>
