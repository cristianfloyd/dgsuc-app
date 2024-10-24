<x-filament::modal
    :heading="__('Seleccionar Panel')"
    :description="__('Elige el panel al que deseas acceder')"
    :width="'lg'"
    wire:model="isOpen">

    <div class="grid grid-cols-1 gap-4 mt-4 sm:grid-cols-2">
        @foreach ($panels as $panel)
            <a
                href="{{ $panel['url'] }}"
                class="flex items-center p-4 transition-colors rounded-lg shadow-sm outline-none bg-white hover:bg-gray-50 focus:bg-gray-50"
            >
                <x-dynamic-component
                    :component="$panel['icon']"
                    :class="'w-6 h-6 text-' . $panel['color'] . '-600'"
                />

                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">
                        {{ $panel['name'] }}
                    </p>
                </div>
            </a>
        @endforeach
    </div>

    <x-slot name="footer">
        <x-filament::button
            color="gray"
            wire:click="$set('isOpen', false)"
        >
            {{ __('Cerrar') }}
        </x-filament::button>
    </x-slot>
</x-filament::modal>
