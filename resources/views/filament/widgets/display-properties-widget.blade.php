<x-filament::widget>
    <x-filament::card>
        <h2 class="text-lg font-medium">Propiedades de Embargo Actuales</h2>
        <dl class="mt-2 grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
            @foreach($properties as $key => $value)
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ is_array($value) ? implode(', ', $value) : $value }}</dd>
                </div>
            @endforeach
        </dl>
    </x-filament::card>
</x-filament::widget>

