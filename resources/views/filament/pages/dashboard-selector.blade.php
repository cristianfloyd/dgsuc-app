<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($this->getPanels() as $panel)
            <div class="relative group">
                <div class="h-full rounded-xl border border-gray-200 bg-white shadow-sm transition duration-200 hover:shadow-lg hover:scale-[1.02] dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col h-full p-6">
                        <div class="flex items-center gap-x-3 mb-4">
                            <div class="rounded-lg bg-{{ $panel['color'] }}-100 p-2.5 dark:bg-{{ $panel['color'] }}-900/50">
                                <x-filament::icon
                                    :icon="$panel['icon']"
                                    class="h-6 w-6 text-{{ $panel['color'] }}-600 dark:text-{{ $panel['color'] }}-400"
                                />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $panel['name'] }}
                                </h3>
                                @if(isset($panel['badge']))
                                    <span class="inline-flex items-center rounded-md bg-{{ $panel['color'] }}-100 px-2 py-0.5 text-sm font-medium text-{{ $panel['color'] }}-800 dark:bg-{{ $panel['color'] }}-900/50 dark:text-{{ $panel['color'] }}-400">
                                        {{ $panel['badge'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <p class="mt-2 flex-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ $panel['description'] }}
                        </p>

                        <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ $panel['url'] }}"
                               class="inline-flex w-full items-center justify-center rounded-lg bg-{{ $panel['color'] }}-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-{{ $panel['color'] }}-500 focus:outline-none focus:ring-2 focus:ring-{{ $panel['color'] }}-500 focus:ring-offset-2 dark:hover:bg-{{ $panel['color'] }}-400 dark:focus:ring-offset-gray-800 transition duration-200">
                                Acceder al panel
                                <x-filament::icon
                                    icon="heroicon-m-arrow-right"
                                    class="ml-2 h-5 w-5"
                                />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
