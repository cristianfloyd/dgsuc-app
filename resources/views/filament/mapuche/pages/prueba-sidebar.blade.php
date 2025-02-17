<x-filament-panels::page>
    <div class="flex flex-col lg:flex-row gap-4">
        {{-- Sidebar --}}
        <div class="lg:w-1/4">
            <x-filament::section>
                <x-slot name="heading">
                    Sidebar
                </x-slot>
                <div class="p-4 bg-gray-100 dark:bg-gray-800">
                    Contenido del Sidebar
                </div>
            </x-filament::section>
        </div>

        {{-- Contenido principal --}}
        <div class="lg:w-3/4">
            <x-filament::section>
                <x-slot name="heading">
                    Contenido Principal
                </x-slot>
                <div class="p-4">
                    Este es el contenido principal que ocupa 9 columnas
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
