<x-filament-widgets::widget>
    <x-filament::card>
        <div class="space-y-2">
            <h2 class="text-lg font-bold">Conexiones de Base de Datos</h2>

            @foreach($this->getConnections() as $name => $connection)
                <div class="p-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <div class="font-medium">{{ ucfirst($name) }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Base de datos: {{ $connection['name'] }}<br>
                        Driver: {{ $connection['driver'] }}<br>
                        Estado: <span class="{{ $connection['status'] === 'Conectado' ? 'text-green-600' : 'text-red-500' }}">
                            {{ $connection['status'] }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::card>
</x-filament-widgets::widget>
