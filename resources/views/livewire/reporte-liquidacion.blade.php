<div>
    <div>
        <div>
            <input wire:model.live.debounce.300ms ="legajo" placeholder="LEGAJO">
        </div>
        <div>
            <label for="search">Buscar:</label>
            <input type="text" id="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por Apellido o Numero de Cargo">
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="table table-xs">
            <thead>
                <tr>
                    <th>Nro Liqui</th>
                    <th>Id Legajo</th>
                    <th>Numero Cargo</th>
                    <th>Id Dependencia</th>
                    <th>Categoria</th>
                    <th>Monto</th>
                    <th>Id Escalafon</th>
                    <th>Id Oficina De Pago</th>
                    <th>Id Fuente Financiamento</th>
                    <th>Cuil</th>
                    <th>Apellido</th>
                    <th>Fecha Nacimiento</th>
                    <th>Id Sexo</th>
                    <th>Id Agrupamiento</th>
                    <th>Id Dedicacion</th>
                    <th>Tipo Cargo Descripcion</th>
                </tr>
            </thead>
            </tbody>
                @foreach ($liquidaciones as $liquidacion )
                    <tr wire:key="{{$liquidacion->id_legajo.'-'.$liquidacion->numero_cargo}}">
                        <td>{{ $liquidacion->nro_liqui }}</td>
                        <td>{{ $liquidacion->id_legajo }}</td>
                        <td>{{ $liquidacion->numero_cargo }}</td>
                        <td>{{ $liquidacion->id_dependencia }}</td>
                        <td>{{ $liquidacion->categoria }}</td>
                        <td>{{ $liquidacion->monto_total }}</td>
                        <td>{{ $liquidacion->id_escalafon }}</td>
                        <td>{{ $liquidacion->id_oficina_pago }}</td>
                        <td>{{ $liquidacion->id_fuente_financiamento }}</td>
                        <td>{{ $liquidacion->cuil }}</td>
                        <td>{{ $liquidacion->apellido }}</td>
                        <td>{{ $liquidacion->fecha_nacimiento }}</td>
                        <td>{{ $liquidacion->id_sexo }}</td>
                        <td>{{ $liquidacion->id_agrupamiento }}</td>
                        <td>{{ $liquidacion->id_dedicacion }}</td>
                        <td>{{ $liquidacion->tipo_cargo_descripcion }}</td>
                    </tr>
                @endforeach
            <tbody>
        </table>
    </div>
    <div>
        <select wire:model.live ="perPage"  id="perPage">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
        </select>
    </div>
    <div>
        {{ $liquidaciones->links()}}
    </div>
</div>
