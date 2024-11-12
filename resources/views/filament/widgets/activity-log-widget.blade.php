<x-filament::widget>
    <x-filament::card>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium">Registro de Actividad</h2>
                <span class="text-sm text-gray-500">Actualizado: {{ now()->format('H:i:s') }}</span>
            </div>

            {{-- Usuarios Activos --}}
            <div class="space-y-4">
                <h3 class="text-sm font-medium text-gray-500">Usuarios Activos</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Usuario</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Aplicación</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">IP</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold text-gray-900">Inicio Sesión</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($this->getActivityData()['usuarios_activos'] as $usuario)
                            <tr>
                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">{{ $usuario->usuario }}</td>
                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">{{ $usuario->application_name }}</td>
                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">{{ $usuario->ip }}</td>
                                <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-500">{{ $usuario->inicio_sesion }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Estadísticas de Actividad --}}
            <div class="mt-4">
                <h3 class="text-sm font-medium text-gray-500">Estadísticas por Tabla</h3>
                <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach($this->getActivityData()['estadisticas'] as $stat)
                    <div class="rounded-lg bg-slate-800 p-4 shadow-sm">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium">{{ $stat->tabla }}</span>
                        </div>
                        <div class="mt-1 text-xs text-gray-500">
                            <div>Inserciones: {{ $stat->inserciones }}</div>
                            <div>Actualizaciones: {{ $stat->actualizaciones }}</div>
                            <div>Eliminaciones: {{ $stat->eliminaciones }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
