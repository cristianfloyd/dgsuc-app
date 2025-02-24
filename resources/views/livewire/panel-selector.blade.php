<div class="min-h-screen transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Encabezado -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Panel de Control
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Seleccione el módulo al que desea acceder
            </p>
        </div>

        <!-- Filtros -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div class="relative flex-1 max-w-xs">
                <input
                    wire:model.live="search"
                    type="text"
                    class="w-full pl-10 pr-4 py-2 rounded-lg border-0 ring-1 ring-inset ring-gray-300
                           dark:ring-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2
                           focus:ring-inset focus:ring-indigo-600 dark:focus:ring-indigo-500"
                    placeholder="Buscar panel..."
                >
                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400"/>
                </div>
            </div>

            <div class="w-full sm:w-auto">
                <select wire:model.live="selectedCategory"
                    class="w-full border rounded-lg px-4 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                    @foreach ($categories as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Skeleton Loading -->
        <div wire:loading.delay class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach(range(1,6) as $loading)
                <div class="animate-pulse">
                    <div class="rounded-lg h-32 bg-gray-300 dark:bg-gray-700"></div>
                </div>
            @endforeach
        </div>
        
        <!-- Grid de Paneles -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($panels as $panel)
                <a href="{{ $panel['url'] }}"
                    class="transform transition-all duration-300 hover:scale-102 hover:shadow-lg"
                    x-data="{ show: false }" x-show="show" x-init="setTimeout(() => show = true, {{ $loop->index * 100 }})"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform translate-y-4"
                    x-transition:enter-end="opacity-100 transform translate-y-0">
                    <div
                        class="relative rounded-lg shadow-md overflow-hidden {{ $panel['bgColor'] }}
        hover:bg-opacity-90 group cursor-pointer">
                        <div
                            class="absolute inset-0 bg-gradient-to-b from-black/5 to-black/20 opacity-0
            group-hover:opacity-100 transition-opacity duration-300">
                        </div>
                        <div class="relative p-6 transform group-hover:scale-[1.02] transition-transform duration-300">

                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <x-dynamic-component :component="$panel['icon']" class="w-8 h-8 text-white"/>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-semibold text-white">{{ $panel['name'] }}</h3>
                                        @if(isset($panel['description']))
                                            <p class="mt-1 text-sm text-white/80">{{ $panel['description'] }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if(isset($panel['badge']))
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-white/20 text-white">
                                        {{ $panel['badge'] }}
                                    </span>
                                @endif
                            </div>

                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <style>
        * {
            transition-property: background-color, border-color, color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }
    </style>
</div>
