<div>
    <x-filament::modal width="md" wire:model="isOpen">
        <x-slot name="header">
            <x-filament::modal.heading>
                Seleccionar Panel
            </x-filament::modal.heading>
        </x-slot>

        <div class="grid grid-cols-1 gap-4 p-4">
            @foreach ($panels as $panel)
                <button
                    wire:click="switchPanel('{{ $panel['id'] }}')"
                    class="flex items-center p-4 transition bg-white border rounded-lg shadow-sm hover:bg-gray-50 border-gray-200"
                >
                    <x-dynamic-component
                        :component="$panel['icon']"
                        class="w-6 h-6 text-{{ $panel['color'] }}-600"
                    />
                    <span class="ml-3 text-sm font-medium text-gray-900">
                        {{ $panel['name'] }}
                    </span>
                </button>
            @endforeach
        </div>
    </x-filament::modal>
</div>
