<table>
    <thead>
        <tr>
            <th>CUIL</th>
            <th>Apellido y Nombre</th>
            <th>Cónyuge</th>
            <th>Cant. Hijos</th>
            <th>Situación</th>
            <th>Condición</th>
            <th>Actividad</th>
            <th>Zona</th>
            <th>Porc. Aporte</th>
            <th>Mod. Contratación</th>
            <th>Obra Social</th>
            <th>Cant. Adherentes</th>
            <th>Rem. Total</th>
            <th>Rem. Imponible 1</th>
            <th>Asig. Fam. Pagadas</th>
            <th>Aporte Voluntario</th>
            <th>Imp. Adic. OS</th>
            <th>Exc. Aporte SS</th>
            <th>Exc. Aporte OS</th>
            <th>Provincia</th>
            <th>Rem. Imponible 2</th>
            <th>Rem. Imponible 3</th>
            <th>Rem. Imponible 4</th>
            <th>Siniestrado</th>
            <th>Reducción</th>
            <th>Recomp. LRT</th>
            <th>Tipo Empresa</th>
            <th>Aporte Adic. OS</th>
            <th>Régimen</th>
            <th>Sit. Revista 1</th>
            <th>Día Inicio SR1</th>
            <th>Sit. Revista 2</th>
            <th>Día Inicio SR2</th>
            <th>Sit. Revista 3</th>
            <th>Día Inicio SR3</th>
            <th>Sueldo Adicional</th>
            <th>SAC</th>
            <th>Horas Extras</th>
            <th>Zona Desfavorable</th>
            <th>Vacaciones</th>
            <th>Días Trabajados</th>
            <th>Rem. Imponible 5</th>
            <th>Convencionado</th>
            <th>Rem. Imponible 6</th>
            <th>Tipo Operación</th>
            <th>Adicionales</th>
            <th>Premios</th>
            <th>Rem. Dec. 788</th>
            <th>Rem. Imponible 7</th>
            <th>Nro. Horas Extras</th>
            <th>Conceptos No Remunerativos</th>
            <th>Maternidad</th>
            <th>Rectificación Remuneración</th>
            <th>Rem. Imponible 9</th>
            <th>Contribución Diferencial</th>
            <th>Horas Trabajadas</th>
            <th>Seguro</th>
            <th>Ley</th>
            <th>Inc. Salarial</th>
            <th>Rem. Imponible 11</th>
            <th>Diferencia Rem.</th>
        </tr>
    </thead>
    <tbody>
        @foreach($registros as $registro)
            <tr>
                <td>{{ $registro->cuil }}</td>
                <td>{{ $registro->apnom }}</td>
                <td>{{ $registro->conyuge ? 'Sí' : 'No' }}</td>
                <td>{{ $registro->cant_hijos }}</td>
                <td>{{ $registro->cod_situacion }}</td>
                <td>{{ $registro->cod_cond }}</td>
                <td>{{ $registro->cod_act }}</td>
                <td>{{ $registro->cod_zona }}</td>
                <td>{{ $registro->porc_aporte }}</td>
                <td>{{ $registro->cod_mod_cont }}</td>
                <td>{{ $registro->cod_os }}</td>
                <td>{{ $registro->cant_adh }}</td>
                <td>{{ $registro->rem_total }}</td>
                <td>{{ $registro->rem_impo1 }}</td>
                <td>{{ $registro->asig_fam_pag }}</td>
                <td>{{ $registro->aporte_vol }}</td>
                <td>{{ $registro->imp_adic_os }}</td>
                <td>{{ $registro->exc_aport_ss }}</td>
                <td>{{ $registro->exc_aport_os }}</td>
                <td>{{ $registro->prov }}</td>
                <td>{{ $registro->rem_impo2 }}</td>
                <td>{{ $registro->rem_impo3 }}</td>
                <td>{{ $registro->rem_impo4 }}</td>
                <td>{{ $registro->cod_siniestrado }}</td>
                <td>{{ $registro->marca_reduccion ? 'Sí' : 'No' }}</td>
                <td>{{ $registro->recomp_lrt }}</td>
                <td>{{ $registro->tipo_empresa }}</td>
                <td>{{ $registro->aporte_adic_os }}</td>
                <td>{{ $registro->regimen }}</td>
                <td>{{ $registro->sit_rev1 }}</td>
                <td>{{ $registro->dia_ini_sit_rev1 }}</td>
                <td>{{ $registro->sit_rev2 }}</td>
                <td>{{ $registro->dia_ini_sit_rev2 }}</td>
                <td>{{ $registro->sit_rev3 }}</td>
                <td>{{ $registro->dia_ini_sit_rev3 }}</td>
                <td>{{ $registro->sueldo_adicc }}</td>
                <td>{{ $registro->sac }}</td>
                <td>{{ $registro->horas_extras }}</td>
                <td>{{ $registro->zona_desfav }}</td>
                <td>{{ $registro->vacaciones }}</td>
                <td>{{ $registro->cant_dias_trab }}</td>
                <td>{{ $registro->rem_impo5 }}</td>
                <td>{{ $registro->convencionado ? 'Sí' : 'No' }}</td>
                <td>{{ $registro->rem_impo6 }}</td>
                <td>{{ $registro->tipo_oper }}</td>
                <td>{{ $registro->adicionales }}</td>
                <td>{{ $registro->premios }}</td>
                <td>{{ $registro->rem_dec_788 }}</td>
                <td>{{ $registro->rem_imp7 }}</td>
                <td>{{ $registro->nro_horas_ext }}</td>
                <td>{{ $registro->cpto_no_remun }}</td>
                <td>{{ $registro->maternidad }}</td>
                <td>{{ $registro->rectificacion_remun }}</td>
                <td>{{ $registro->rem_imp9 }}</td>
                <td>{{ $registro->contrib_dif }}</td>
                <td>{{ $registro->hstrab }}</td>
                <td>{{ $registro->seguro ? 'Sí' : 'No' }}</td>
                <td>{{ $registro->ley }}</td>
                <td>{{ $registro->incsalarial }}</td>
                <td>{{ $registro->remimp11 }}</td>
                <td>{{ $registro->diferencia_rem }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
