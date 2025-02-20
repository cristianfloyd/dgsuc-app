<x-filament-widgets::widget>
    @if($this->shouldLoad())
        <x-filament::section collapsible>
            <x-slot name="heading">
                Totales del Período
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-filament::card>
                    <div @class([
                        'flex items-center gap-x-2',
                        'dark:text-gray-200' => config('filament.dark_mode'),
                    ])>
                        <span class="text-sm font-medium">Total Aportes</span>
                    </div>

                    <div @class([
                        'text-3xl font-semibold tracking-tight',
                        'text-gray-950 dark:text-white' => config('filament.dark_mode'),
                    ])>
                        {{ money($totales['total_aportes'] ?? 0, 'ARS') }}
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div @class([
                        'flex items-center gap-x-2',
                        'dark:text-gray-200' => config('filament.dark_mode'),
                    ])>
                        <span class="text-sm font-medium">Total Contribuciones</span>
                    </div>

                    <div @class([
                        'text-3xl font-semibold tracking-tight',
                        'text-gray-950 dark:text-white' => config('filament.dark_mode'),
                    ])>
                        {{ money($totales['total_contribuciones'] ?? 0, 'ARS') }}
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div @class([
                        'flex items-center gap-x-2',
                        'dark:text-gray-200' => config('filament.dark_mode'),
                    ])>
                        <span class="text-sm font-medium">Total Remunerativo Imponible</span>
                    </div>

                    <div @class([
                        'text-3xl font-semibold tracking-tight',
                        'text-gray-950 dark:text-white' => config('filament.dark_mode'),
                    ])>
                        {{ money($totales['total_remunerativo'] ?? 0, 'ARS') }}
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div @class([
                        'flex items-center gap-x-2',
                        'dark:text-gray-200' => config('filament.dark_mode'),
                    ])>
                        <span class="text-sm font-medium">Total No Remunerativo Imponible</span>
                    </div>

                    <div @class([
                        'text-3xl font-semibold tracking-tight',
                        'text-gray-950 dark:text-white' => config('filament.dark_mode'),
                    ])>
                        {{ money($totales['total_no_remunerativo'] ?? 0, 'ARS') }}
                    </div>
                </x-filament::card>
            </div>
        </x-filament::section>
    @endif
</x-filament-widgets::widget>
