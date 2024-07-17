<div>
    @if(!empty($results))
        <table>
            <thead>
                <tr>
                    <th>Nro Legajo</th>
                    <th>Nro Liqui</th>
                    <th>Sino Cerra</th>
                    <th>Estado Liquidacion</th>
                    <th>Nro Cargo</th>
                    <th>Periodo Fiscal</th>
                    <th>Tipo de Registro</th>
                    <th>Codigo Movimiento</th>
                    <th>CUIL</th>
                    <th>Trabajador Agropecuario</th>
                    <th>Modalidad Contrato</th>
                    <th>Inicio Rel Lab</th>
                    <th>Fin Rel Lab</th>
                    <th>Obra Social</th>
                    <th>Codigo Situacion Baja</th>
                    <th>Fecha Tel Renuncia</th>
                    <th>Retribucion Pactada</th>
                    <th>Modalidad Liquidacion</th>
                    <th>Domicilio</th>
                    <th>Actividad</th>
                    <th>Puesto</th>
                    <th>Rectificacion</th>
                    <th>CCCT</th>
                    <th>Tipo Servicio</th>
                    <th>Categoria</th>
                    <th>Fecha Suspencion Servicios</th>
                    <th>Numero Form Agrop</th>
                    <th>COVID</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $result)
                    <tr>
                        <td>{{ $result->nro_legaj }}</td>
                        <td>{{ $result->nro_liqui }}</td>
                        <td>{{ $result->sino_cerra }}</td>
                        <td>{{ $result->desc_estado_liquidacion }}</td>
                        <td>{{ $result->nro_cargo }}</td>
                        <td>{{ $result->periodo_fiscal }}</td>
                        <td>{{ $result->tipo_de_registro }}</td>
                        <td>{{ $result->codigo_movimiento }}</td>
                        <td>{{ $result->cuil }}</td>
                        <td>{{ $result->trabajador_agropecuario }}</td>
                        <td>{{ $result->modalidad_contrato }}</td>
                        <td>{{ $result->inicio_rel_lab }}</td>
                        <td>{{ $result->fin_rel_lab }}</td>
                        <td>{{ $result->obra_social }}</td>
                        <td>{{ $result->codigo_situacion_baja }}</td>
                        <td>{{ $result->fecha_tel_renuncia }}</td>
                        <td>{{ $result->retribucion_pactada }}</td>
                        <td>{{ $result->modalidad_liquidaicon }}</td>
                        <td>{{ $result->domicilio }}</td>
                        <td>{{ $result->actividad }}</td>
                        <td>{{ $result->puesto }}</td>
                        <td>{{ $result->rectificacion }}</td>
                        <td>{{ $result->ccct }}</td>
                        <td>{{ $result->tipo_servicio }}</td>
                        <td>{{ $result->categoria }}</td>
                        <td>{{ $result->fecha_suspencion_servicios }}</td>
                        <td>{{ $result->numero_form_agrop }}</td>
                        <td>{{ $result->covid }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No results found.</p>
    @endif
</div>

