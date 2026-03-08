@php
    $colorClasses = \App\Filament\Pages\DashboardSelector::getColorClasses();
@endphp
<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($this->getPanels() as $panel)
            @php
                $c = $colorClasses[$panel['color']] ?? $colorClasses['slate'];
            @endphp
            <div class="relative group">
                <div class="h-full rounded-xl border border-gray-200 bg-white shadow-sm transition duration-200 hover:shadow-lg hover:scale-[1.02] dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col h-full p-6">
                        <div class="flex items-center gap-x-3 mb-4">
                            <div class="rounded-lg p-2.5 {{ $c['icon_bg'] }}">
                                <x-filament::icon
                                    :icon="$panel['icon']"
                                    class="h-6 w-6 {{ $c['icon_text'] }}"
                                />
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $panel['name'] }}
                                </h3>
                                @if(isset($panel['badge']))
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-sm font-medium {{ $c['badge_bg'] }} {{ $c['badge_text'] }}">
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
                               class="inline-flex w-full items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition duration-200 {{ $c['button'] }}">
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
