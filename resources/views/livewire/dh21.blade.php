<div>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center">DH21</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nro Liqui</th>
                            <th>Nro Legaj</th>
                            <th>Nro Cargo</th>
                            <th>Codn Conce</th>
                            <th>Impp Conce</th>
                            <th>Tipo Conce</th>
                            <th>Nov1 Conce</th>
                            <th>Nov2 Conce</th>
                            <th>Nro Orimp</th>
                            <th>Tipoescalafon</th>
                            <th>Nrogrupoesc</th>
                            <th>Codigoescalafon</th>
                            <th>Codc Regio</th>
                            <th>Codc Uacad</th>
                            <th>Codn Area</th>
                            <th>Codn Subar</th>
                            <th>Codn Fuent</th>
                            <th>Codn Progr</th>
                            <th>Codn Subpr</th>
                            <th>Codn Proye</th>
                            <th>Codn Activ</th>
                            <th>Codn Obra</th>
                            <th>Codn Final</th>
                            <th>Codn Funci</th>
                            <th>Ano Retro</th>
                            <th>Mes Retro</th>
                            <th>Detalle Novedad</th>
                            <th>Codn Grupo Presup</th>
                            <th>Tipo Ejercicio</th>
                            <th>Codn Subsubar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dh21s as $dh21)
                        <tr>
                            <td>{{ $dh21->nro_liqui }}</td>
                            <td>{{ $dh21->nro_legaj }}</td>
                            <td>{{ $dh21->nro_cargo }}</td>
                            <td>{{ $dh21->codn_conce }}</td>
                            <td>{{ $dh21->impp_conce }}</td>
                            <td>{{ $dh21->tipo_conce }}</td>
                            <td>{{ $dh21->nov1_conce }}</td>
                            <td>{{ $dh21->nov2_conce }}</td>
                            <td>{{ $dh21->nro_orimp }}</td>
                            <td>{{ $dh21->tipoescalafon }}</td>
                            <td>{{ $dh21->nrogrupoesc }}</td>
                            <td>{{ $dh21->codigoescalafon }}</td>
                            <td>{{ $dh21->codc_regio }}</td>
                            <td>{{ $dh21->codc_uacad }}</td>
                            <td>{{ $dh21->codn_area }}</td>
                            <td>{{ $dh21->codn_subar }}</td>
                            <td>{{ $dh21->codn_fuent }}</td>
                            <td>{{ $dh21->codn_progr }}</td>
                            <td>{{ $dh21->codn_subpr }}</td>
                            <td>{{ $dh21->codn_proye }}</td>
                            <td>{{ $dh21->codn_activ }}</td>
                            <td>{{ $dh21->codn_obra }}</td>
                            <td>{{ $dh21->codn_final }}</td>
                            <td>{{ $dh21->codn_funci }}</td>
                            <td>{{ $dh21->ano_retro }}</td>
                            <td>{{ $dh21->mes_retro }}</td>
                            <td>{{ $dh21->detalle_novedad }}</td>
                            <td>{{ $dh21->codn_grupo_presup }}</td>
                            <td>{{ $dh21->tipo_ejercicio }}</td>
                            <td>{{ $dh21->codn_subsubar }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
        <div class="row">
            {{-- Agregar selector de paginacion --}}
            <div class="col-md-12">
                <select wire:model.live="perPage" class="form-control">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                </select>
            </div>
            <div class="row">
                {{ $dh21s->links() }}
            </div>
</div>
