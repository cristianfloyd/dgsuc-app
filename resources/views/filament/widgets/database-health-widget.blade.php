    <x-filament::widget>
        <x-filament::section>
            <h4>Database</h4>
        </x-filament::section>
        <x-filament::card>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-medium">Estado de la Base de Datos</h2>
                    <span class="text-sm text-gray-500">Actualizado: {{ now()->format('H:i:s') }}</span>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {{-- Tamaño DB --}}
                    <div class="rounded-lg bg-slate-600 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Esquema mapuche</span>
                            <span class="text-{{ $this->getMetrics()['size_db']['status'] }}-500">
                                {{ $this->getMetrics()['size_db']['value'] }}
                                {{ $this->getMetrics()['size_db']['unit'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Estado Conexión --}}
                    <div class="rounded-lg bg-slate-600 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-500">Latencia</span>
                            <span
                                class="text-{{ $this->getMetrics()['conexion_status']['status'] === 'success' ? 'green' : 'red' }}-500">
                                {{ $this->getMetrics()['conexion_status']['latencia'] }}ms
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Tablas más grandes --}}
                <div class="mt-4">
                    <h3 class="text-sm font-medium text-gray-500">Top 5 Tablas por Tamaño</h3>
                    <div class="mt-2 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead>
                                        <tr>
                                            <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Tabla
                                            </th>
                                            <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Tamaño
                                            </th>
                                            <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">
                                                Registros</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach ($this->getMetrics()['tablas_info'] as $tabla)
                                            <tr>
                                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                                    {{ $tabla->nombre }}</td>
                                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                                    {{ $tabla->total_size }} MB</td>
                                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">
                                                    {{ number_format($tabla->registros) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::card>
    </x-filament::widget>
