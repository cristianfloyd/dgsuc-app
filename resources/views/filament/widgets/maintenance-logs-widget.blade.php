<x-filament::widget>
    <x-filament::card>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium">Logs de Mantenimiento</h2>
                <span class="text-sm text-gray-500">Actualizado: {{ now()->format('H:i:s') }}</span>
            </div>

            {{-- Procesos Pendientes --}}
            <div class="space-y-4">
                <h3 class="text-sm font-medium text-gray-500">Procesos Activos</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">PID</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Query</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Estado</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Duración</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($this->getMaintenanceLogs()['procesos'] as $proceso)
                            <tr>
                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">{{ $proceso->pid }}</td>
                                <td class="px-3 py-2 text-sm text-gray-500">{{ Str::limit($proceso->query, 50) }}</td>
                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">{{ $proceso->state }}</td>
                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">{{ $proceso->duration }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Estado de Vacuum --}}
            <div class="mt-4">
                <h3 class="text-sm font-medium text-gray-500">Estado de Vacuum</h3>
                <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach($this->getMaintenanceLogs()['vacuums'] as $vacuum)
                    <div class="rounded-lg bg-slate-700 p-4 shadow-sm">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium">{{ $vacuum->relname }}</span>
                            <div class="group relative">
                                <span class="text-sm text-gray-500 cursor-help">
                                    {{ $vacuum->tuplas_muertas }} tuplas muertas
                                </span>
                                <div class="absolute hidden group-hover:block right-0 w-64 p-2 bg-gray-800 text-xs text-white rounded-lg shadow-lg z-50">
                                    <p class="font-bold mb-2">Tuplas muertas son registros que:</p>
                                    <ul class="list-disc pl-4 space-y-1">
                                        <li>Han sido marcados como eliminados pero aún ocupan espacio físico en la tabla</li>
                                        <li>Son versiones antiguas de filas que han sido actualizadas</li>
                                        <li>Ya no son accesibles por ninguna transacción activa</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            Último vacuum: {{ $vacuum->last_vacuum ?? 'Nunca' }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
