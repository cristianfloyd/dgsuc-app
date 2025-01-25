<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($this->getPanels() as $panel)
            <x-filament::card>
                <div class="flex items-center">
                    <x-filament::icon
                        :icon="$panel['icon']"
                        class="h-8 w-8 text-primary-500"
                    />

                    <div class="ml-4">
                        <h3 class="text-lg font-medium">
                            {{ $panel['name'] }}
                        </h3>
                        <p class="text-sm text-gray-500">
                            {{ $panel['description'] }}
                        </p>
                    </div>
                </div>

                <div class="mt-4">
                    <x-filament::button
                        :href="$panel['url']"
                        color="primary"
                        class="w-full"
                    >
                        Acceder
                    </x-filament::button>
                </div>
            </x-filament::card>
        @endforeach
    </div>
</x-filament-panels::page>
