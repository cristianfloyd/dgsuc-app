<x-filament::widget>
    <x-filament::card>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium">Estado del Sistema</h2>
                <span class="text-sm text-gray-500">Actualizado: {{ now()->format('H:i:s') }}</span>
            </div>

            {{-- Métricas de Conexiones --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-lg bg-slate-600 p-4 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Conexiones</h3>
                    <div class="mt-2 flex items-baseline justify-between">
                        <div class="text-2xl font-semibold text-gray-900">
                            {{ $this->getSystemMetrics()['conexiones']['activas'] }}
                        </div>
                        <div class="text-sm text-gray-500">
                            de {{ $this->getSystemMetrics()['conexiones']['max'] }}
                        </div>
                    </div>
                </div>

                {{-- Cache Hit Ratio --}}
                <div class="rounded-lg bg-slate-600 p-4 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Cache Hit Ratio</h3>
                    <div class="mt-2">
                        {{-- @dump($this->getSystemMetrics()) --}}
                        <div class="text-2xl font-semibold text-gray-900">
                            {{ number_format($this->getSystemMetrics()['rendimiento']['cache_hit_ratio'] * 100, 2) }}%
                        </div>
                    </div>
                </div>

                {{-- Tamaño DB --}}
                <div class="rounded-lg bg-slate-600 p-4 shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Tamaño Base de Datos</h3>
                    <div class="mt-2">
                        <div class="text-2xl font-semibold text-gray-900">
                            {{ $this->getSystemMetrics()['espacio'][0]->db_size_pretty }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actividad del Sistema --}}
            <div class="mt-4">
                <h3 class="text-sm font-medium text-gray-500">Actividad Actual</h3>
                <div class="mt-2 flow-root">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Estado</th>
                                    <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Cantidad</th>
                                    <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Duración Máx.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($this->getSystemMetrics()['actividad'] as $activity)
                                <tr>
                                    <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">{{ $activity->state }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">{{ $activity->count }}</td>
                                    <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">{{ $activity->max_duration }}s</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
