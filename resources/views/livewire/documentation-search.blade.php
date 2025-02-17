<div class="relative">
    <div class="relative">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            class="w-full pl-10 pr-4 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
            placeholder="Buscar en la documentación..."
        >
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>

    @if(strlen($search) >= 2)
        <div class="absolute z-10 w-full mt-2 bg-white dark:bg-gray-700 rounded-lg shadow-lg">
            @if($results->isEmpty())
                <div class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                    No se encontraron resultados
                </div>
            @else
                @foreach($results as $result)
                    <a href="{{ route('documentation.show', $result->slug) }}"
                       class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $result->title }}
                        </div>
                        @if(strlen($result->content) > 100)
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ Str::limit(strip_tags($result->content), 100) }}
                            </div>
                        @endif
                    </a>
                @endforeach
            @endif
        </div>
    @endif
</div>
