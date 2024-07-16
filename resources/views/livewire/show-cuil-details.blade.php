<div>
    <div class="container max-w-screen-md mx-auto p-4">
        <h2>Detalles de CUILs no encontrados en AFIP</h2>
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b">CUIL</th>
                    <th class="py-2 px-4 border-b">Nro. Legajo</th>
                    <th class="py-2 px-4 border-b">Nro. Cargo</th>
                    <th class="py-2 px-4 border-b">Periodo Fiscal</th>
                    <!-- Agrega más columnas según sea necesario -->
                </tr>
            </thead>
            <tbody>
                @foreach($this->resultados as $resultado)
                    <tr>
                        <td class="py-2 px-4 border-b">{{ $resultado->cuil }}</td>
                        <td class="py-2 px-4 border-b">{{ $resultado->nro_legaj }}</td>
                        <td class="py-2 px-4 border-b">{{ $resultado->nro_cargo }}</td>
                        <td class="py-2 px-4 border-b">{{ $resultado->periodo_fiscal }}</td>
                        <!-- Agrega más celdas según sea necesario -->
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- {{ $resultados->links() }} --}}
</div>
