<x-filament-panels::page>
    <x-filament::tabs>
        <x-filament::tabs.item
            :active="$activeTab === 'resumen'"
            wire:click="$set('activeTab', 'resumen')">
            <x-slot name="label">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-chart-bar class="h-5 w-5" />
                    <span>Resumen</span>
                </div>
            </x-slot>

            @if($selectedConnection)
                <div class="p-4">
                    <p class="text-lg">Conexión actual: <strong>{{ $selectedConnection }}</strong></p>

                    @if($resultadosControles)
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3 mt-4">
                            <x-filament::card>
                                <h3 class="text-lg font-medium">CUILs con Diferencias</h3>
                                <p class="text-gray-500">{{ count($resultadosControles['cuils'] ?? []) }} registros</p>
                            </x-filament::card>

                            <x-filament::card>
                                <h3 class="text-lg font-medium">Diferencias en Aportes</h3>
                                <p class="text-gray-500">{{ count($resultadosControles['aportes'] ?? []) }} registros</p>
                            </x-filament::card>

                            <x-filament::card>
                                <h3 class="text-lg font-medium">Diferencias en ART</h3>
                                <p class="text-gray-500">{{ count($resultadosControles['art'] ?? []) }} registros</p>
                            </x-filament::card>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center p-4">
                    <p class="text-lg text-gray-500">No hay conexión seleccionada</p>
                </div>
            @endif
        </x-filament::tabs.item>

        <x-filament::tabs.item
            :active="$activeTab === 'cuils'"
            wire:click="$set('activeTab', 'cuils')">
            <x-slot name="label">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user-group class="h-5 w-5" />
                    <span>CUILs</span>
                </div>
            </x-slot>
            {{-- Aquí irá la tabla de CUILs --}}
        </x-filament::tabs.item>

        <x-filament::tabs.item
            :active="$activeTab === 'aportes'"
            wire:click="$set('activeTab', 'aportes')">
            <x-slot name="label">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-currency-dollar class="h-5 w-5" />
                    <span>Aportes</span>
                </div>
            </x-slot>
            {{-- Aquí irá la tabla de Aportes --}}
        </x-filament::tabs.item>

        <x-filament::tabs.item
            :active="$activeTab === 'art'"
            wire:click="$set('activeTab', 'art')">
            <x-slot name="label">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-shield-check class="h-5 w-5" />
                    <span>ART</span>
                </div>
            </x-slot>
            {{-- Aquí irá la tabla de ART --}}
        </x-filament::tabs.item>
    </x-filament::tabs>
</x-filament-panels::page>
