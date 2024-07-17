<div>
    <div class="container max-w-screen-md mx-auto p-4">
        <h2>Detalles de CUILs no encontrados en AFIP</h2>
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
    </div>
    <div class="mt-4">
        {{ $paginatedResults->links() }}
    </div>
</div>
