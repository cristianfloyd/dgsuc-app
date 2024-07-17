<div>
    <div class="container max-w-screen-md mx-auto p-4">
        <h2>Detalles de CUILs no encontrados en AFIP</h2>

        @if($isLoading)
            <div class="mb-4">
                <p>Cargando datos... {{ number_format($progress, 2) }}% completado</p>
                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @else
            @if(!empty($failedCuils))
                <div class="mb-4">
                    <p class="text-red-500">Los siguientes CUILs fallaron al obtener detalles:</p>
                    <ul>
                        @foreach($failedCuils as $cuil)
                            <li>{{ $cuil }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">CUIL</th>
                        <th class="py-2 px-4 border-b">Nro. Legajo</th>
                        <th class="py-2 px-4 border-b">Nro. Liquidación</th>
                        <!-- Agrega más columnas según sea necesario -->
                    </tr>
                </thead>
                <tbody>
                    @foreach($paginatedResults as $resultado)
                        <tr>
                            <td class="py-2 px-4 border-b">{{ $resultado->cuil }}</td>
                            <td class="py-2 px-4 border-b">{{ $resultado->nro_legaj }}</td>
                            <td class="py-2 px-4 border-b">{{ $resultado->nro_liqui }}</td>
                            <!-- Agrega más celdas según sea necesario -->
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">

            </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('livewire:load', function () {
        Livewire.on('progressUpdated', progress => {
            // Puedes agregar animaciones o actualizaciones adicionales aquí si lo deseas
        });

        Livewire.on('batchLoaded', () => {
            @this.loadBatch();
        });

        @this.loadBatch();
    });
</script>
