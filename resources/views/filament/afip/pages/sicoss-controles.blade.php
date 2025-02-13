<x-filament-panels::page>
    <x-filament::tabs>
        {{-- Resumen de Resultados --}}
        <x-filament::tabs.item
            :active="$activeTab === 'resumen'"
            wire:click="$set('activeTab', 'resumen')">
            <x-slot name="label">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-chart-bar class="h-5 w-5" />
                    <span>Resumen</span>
                </div>
            </x-slot>

            @if($resultadosControles)
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 mt-4">
                    {{-- Card CUILs --}}
                    <x-filament::card>
                        <h3 class="text-lg font-medium">CUILs con Diferencias</h3>
                        <p class="text-sm text-gray-500">
                            {{ count($resultadosControles['resultados']['cuils_faltantes'] ?? []) }} registros
                        </p>
                    </x-filament::card>

                    {{-- Card Aportes --}}
                    <x-filament::card>
                        <h3 class="text-lg font-medium">Diferencias en Aportes</h3>
                        <p class="text-sm text-gray-500">
                            {{ count($resultadosControles['resultados']['diferencias_encontradas'] ?? []) }} registros
                        </p>
                    </x-filament::card>

                    {{-- Card ART --}}
                    <x-filament::card>
                        <h3 class="text-lg font-medium">Diferencias en ART</h3>
                        <p class="text-sm text-gray-500">
                            {{ count($resultadosControles['resultados']['diferencias_art'] ?? []) }} registros
                        </p>
                    </x-filament::card>
                </div>
            @else
                <div class="text-center py-4">
                    <p class="text-gray-500">Seleccione una conexión y ejecute los controles para ver resultados</p>
                </div>
            @endif
        </x-filament::tabs.item>

        {{-- Tabla de CUILs --}}
        <x-filament::tabs.item
            :active="$activeTab === 'cuils'"
            wire:click="$set('activeTab', 'cuils')">
            <x-slot name="label">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-user-group class="h-5 w-5" />
                    <span>CUILs</span>
                </div>
            </x-slot>

            {{ $this->table }}
        </x-filament::tabs.item>

        {{-- Tabla de Aportes --}}
        <x-filament::tabs.item
            :active="$activeTab === 'aportes'"
            wire:click="$set('activeTab', 'aportes')">
            <x-slot name="label">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-currency-dollar class="h-5 w-5" />
                    <span>Aportes</span>
                </div>
            </x-slot>

            {{ $this->table }}
        </x-filament::tabs.item>

        {{-- Tabla de ART --}}
        <x-filament::tabs.item
            :active="$activeTab === 'art'"
            wire:click="$set('activeTab', 'art')">
            <x-slot name="label">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-shield-check class="h-5 w-5" />
                    <span>ART</span>
                </div>
            </x-slot>

            {{ $this->table }}
        </x-filament::tabs.item>
    </x-filament::tabs>
</x-filament-panels::page>
