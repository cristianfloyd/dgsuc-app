<div>
    <div class="container max-w-screen-md mx-auto p-4">
        <h2>Detalles de CUILs no encontrados en AFIP</h2>
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b">CUIL</th>
                    {{-- <th class="py-2 px-4 border-b">Nro. Legajo</th>
                    <th class="py-2 px-4 border-b">Nro. Cargo</th>
                    <th class="py-2 px-4 border-b">Periodo Fiscal</th> --}}
                    <!-- Agrega más columnas según sea necesario -->
                </tr>
            </thead>
            <tbody>
                @foreach($this->resultados as $key => $resultado)
                    <tr>
                        <td class="py-2 px-4 border-b">{{ $resultado[$key]['cuil'] }}</td>
                        <td class="py-2 px-4 border-b">{{ $resultado[$key]['nro_legaj'     ] }}</td>
                        <td class="py-2 px-4 border-b">{{ $resultado[$key]['nro_cargo'     ] }}</td>
                        <td class="py-2 px-4 border-b">{{ $resultado[$key]['periodo_fiscal'] }}</td>
                        <!-- Agrega más celdas según sea necesario -->
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div>
        <p>paginas</p>
        {{ $this->resultados }}
    </div>
    <div>
        @if(!empty($failedCuils))
            <h2>
                {{dd($failedCuils)}}
            </h2>
        @endif
    </div>
</div>
