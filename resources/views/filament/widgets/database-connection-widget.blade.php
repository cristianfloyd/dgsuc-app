<x-filament::widget>
    <x-filament::card>
        <div class="space-y-2">
            <h2 class="text-lg font-bold">Conexiones de Base de Datos</h2>

            <div class="grid grid-cols-2 gap-4">
                @foreach($this->getConnections() as $name => $connection)
                    <div class="p-2 bg-gray-100 dark:bg-gray-800 rounded-lg mb-2">
                        <div class="font-medium text-primary-600">{{ ucfirst($name) }}</div>
                        <div class="text-sm space-y-1">
                            @foreach($this->getColumns() as $key => $label)
                                @if(isset($connection[$key]))
                                    <div class="flex items-center">
                                        <span class="text-gray-600 dark:text-gray-400">{{ $label }}:</span>
                                        <span class="ml-2 font-bold {{ $key === 'status' ? ($connection[$key] === 'Conectado' ? 'text-success-600' : 'text-danger-600') : '' }}">
                                            {{ $connection[$key] }}
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
