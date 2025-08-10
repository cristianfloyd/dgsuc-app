<?php

namespace App\Services;

use App\Contracts\DataMapperInterface;

class DataMapperService implements DataMapperInterface
{
    /**
     * Mapea los datos procesados al modelo AfipSicossDesdeMapuche.
     *
     * @param array $datosProcessados Linea de datos procesados.
     *
     * @return array Los datos mapeados al modelo AfipSicossDesdeMapuche.
     */
    public function mapDataToModel(array $datosProcesados): array
    {
        $datosMapeados = [
            'periodo_fiscal' => $datosProcesados[0],
            'cuil' => $datosProcesados[1],
            'apnom' => $datosProcesados[2],
            'conyuge' => $datosProcesados[3],
            'cant_hijos' => $datosProcesados[4],
            'cod_situacion' => $datosProcesados[5],
            'cod_cond' => $datosProcesados[6],
            'cod_act' => $datosProcesados[7],
            'cod_zona' => $datosProcesados[8],
            'porc_aporte' => $datosProcesados[9],
            'cod_mod_cont' => $datosProcesados[10],
            'cod_os' => $datosProcesados[11],
            'cant_adh' => $datosProcesados[12],
            'rem_total' => $datosProcesados[13],
            'rem_impo1' => $datosProcesados[14],
            'asig_fam_pag' => $datosProcesados[15],
            'aporte_vol' => $datosProcesados[16],
            'imp_adic_os' => $datosProcesados[17],
            'exc_aport_ss' => $datosProcesados[18],
            'exc_aport_os' => $datosProcesados[19],
            'prov' => $datosProcesados[20],
            'rem_impo2' => $datosProcesados[21],
            'rem_impo3' => $datosProcesados[22],
            'rem_impo4' => $datosProcesados[23],
            'cod_siniestrado' => $datosProcesados[24],
            'marca_reduccion' => $datosProcesados[25],
            'recomp_lrt' => $datosProcesados[26],
            'tipo_empresa' => $datosProcesados[27],
            'aporte_adic_os' => $datosProcesados[28],
            'regimen' => $datosProcesados[29],
            'sit_rev1' => $datosProcesados[30],
            'dia_ini_sit_rev1' => $datosProcesados[31],
            'sit_rev2' => $datosProcesados[32],
            'dia_ini_sit_rev2' => $datosProcesados[33],
            'sit_rev3' => $datosProcesados[34],
            'dia_ini_sit_rev3' => $datosProcesados[35],
            'sueldo_adicc' => $datosProcesados[36],
            'sac' => $datosProcesados[37],
            'horas_extras' => $datosProcesados[38],
            'zona_desfav' => $datosProcesados[39],
            'vacaciones' => $datosProcesados[40],
            'cant_dias_trab' => $datosProcesados[41],
            'rem_impo5' => $datosProcesados[42],
            'convencionado' => $datosProcesados[43],
            'rem_impo6' => $datosProcesados[44],
            'tipo_oper' => $datosProcesados[45],
            'adicionales' => $datosProcesados[46],
            'premios' => $datosProcesados[47],
            'rem_dec_788' => $datosProcesados[48],
            'rem_imp7' => $datosProcesados[49],
            'nro_horas_ext' => $datosProcesados[50],
            'cpto_no_remun' => $datosProcesados[51],
            'maternidad' => $datosProcesados[52],
            'rectificacion_remun' => $datosProcesados[53],
            'rem_imp9' => $datosProcesados[54],
            'contrib_dif' => $datosProcesados[55],
            'hstrab' => $datosProcesados[56],
            'seguro' => $datosProcesados[57],
            'ley' => $datosProcesados[58],
            'incsalarial' => $datosProcesados[59],
            'remimp11' => $datosProcesados[60],
        ];
        return $datosMapeados;
    }

    /**
     * Mapea los datos procesados al modelo AfipSicossDesdeMapuche.
     *
     * @param array $line Los datos a mapear.
     *
     * @return array Los datos mapeados al modelo AfipSicossDesdeMapuche.
     */
    public function mapLineToDatabaseModel(array $line): array
    {
        return $this->mapDataToModel($line);
    }

    /**
     * Mapea los datos procesados al modelo AfipSicossDesdeMapuche.
     *
     * @param array $datosProcessados Los datos procesados.
     *
     * @return array Los datos mapeados al modelo AfipSicossDesdeMapuche.
     */
    public function mapearDatosAlModelo(array $datosProcesados): array
    {
        $datosMapeados = [
            'periodo_fiscal' => $datosProcesados[0],
            'cuil' => $datosProcesados[1],
            'apnom' => $datosProcesados[2],
            'conyuge' => $datosProcesados[3],
            'cant_hijos' => $datosProcesados[4],
            'cod_situacion' => $datosProcesados[5],
            'cod_cond' => $datosProcesados[6],
            'cod_act' => $datosProcesados[7],
            'cod_zona' => $datosProcesados[8],
            'porc_aporte' => $datosProcesados[9],
            'cod_mod_cont' => $datosProcesados[10],
            'cod_os' => $datosProcesados[11],
            'cant_adh' => $datosProcesados[12],
            'rem_total' => $datosProcesados[13],
            'rem_impo1' => $datosProcesados[14],
            'asig_fam_pag' => $datosProcesados[15],
            'aporte_vol' => $datosProcesados[16],
            'imp_adic_os' => $datosProcesados[17],
            'exc_aport_ss' => $datosProcesados[18],
            'exc_aport_os' => $datosProcesados[19],
            'prov' => $datosProcesados[20],
            'rem_impo2' => $datosProcesados[21],
            'rem_impo3' => $datosProcesados[22],
            'rem_impo4' => $datosProcesados[23],
            'cod_siniestrado' => $datosProcesados[24],
            'marca_reduccion' => $datosProcesados[25],
            'recomp_lrt' => $datosProcesados[26],
            'tipo_empresa' => $datosProcesados[27],
            'aporte_adic_os' => $datosProcesados[28],
            'regimen' => $datosProcesados[29],
            'sit_rev1' => $datosProcesados[30],
            'dia_ini_sit_rev1' => $datosProcesados[31],
            'sit_rev2' => $datosProcesados[32],
            'dia_ini_sit_rev2' => $datosProcesados[33],
            'sit_rev3' => $datosProcesados[34],
            'dia_ini_sit_rev3' => $datosProcesados[35],
            'sueldo_adicc' => $datosProcesados[36],
            'sac' => $datosProcesados[37],
            'horas_extras' => $datosProcesados[38],
            'zona_desfav' => $datosProcesados[39],
            'vacaciones' => $datosProcesados[40],
            'cant_dias_trab' => $datosProcesados[41],
            'rem_impo5' => $datosProcesados[42],
            'convencionado' => $datosProcesados[43],
            'rem_impo6' => $datosProcesados[44],
            'tipo_oper' => $datosProcesados[45],
            'adicionales' => $datosProcesados[46],
            'premios' => $datosProcesados[47],
            'rem_dec_788' => $datosProcesados[48],
            'rem_imp7' => $datosProcesados[49],
            'nro_horas_ext' => $datosProcesados[50],
            'cpto_no_remun' => $datosProcesados[51],
            'maternidad' => $datosProcesados[52],
            'rectificacion_remun' => $datosProcesados[53],
            'rem_imp9' => $datosProcesados[54],
            'contrib_dif' => $datosProcesados[55],
            'hstrab' => $datosProcesados[56],
            'seguro' => $datosProcesados[57],
            'ley' => $datosProcesados[58],
            'incsalarial' => $datosProcesados[59],
            'remimp11' => $datosProcesados[60],
        ];
        return $datosMapeados;
    }

    /** Mapea los datos procesados al modelo AfipRelacionesActivas.
     * @param array $datosProcessados Los datos procesados.
     *
     * @return array Los datos mapeados al modelo AfipRelacionesActivas.
     */
    public function mapDataToModelAfipRelacionesActivas(array $datosProcesados): array
    {
        $datosMapeados = [

            'periodo_fiscal' => $datosProcesados[0], //periodo fiscal,6
            'codigo_movimiento' => $datosProcesados[1], //codigo movimiento,2
            'tipo_registro' => $datosProcesados[2], //Tipo de registro,2
            'cuil' => $datosProcesados[3], //CUIL del empleado,11
            'marca_trabajador_agropecuario' => $datosProcesados[4], //Marca de trabajador agropecuario,1
            'modalidad_contrato' => $datosProcesados[5], //Modalidad de contrato,3
            'fecha_inicio_relacion_laboral' => $datosProcesados[6], //Fecha de inicio de la rel. Laboral,10
            'fecha_fin_relacion_laboral' => $datosProcesados[7], //Fecha de fin relacion laboral,10
            'codigo_o_social' => $datosProcesados[8], //Código de obra social,6
            'cod_situacion_baja' => $datosProcesados[9], //codigo situacion baja,2
            'fecha_telegrama_renuncia' => $datosProcesados[10], //Fecha telegrama renuncia,10
            'retribucion_pactada' => $datosProcesados[11], //Retribución pactada,15
            'modalidad_liquidacion' => $datosProcesados[12], //Modalidad de liquidación,1
            'suc_domicilio_desem' => $datosProcesados[13], //Sucursal-Domicilio de desempeño,5
            'actividad_domicilio_desem' => $datosProcesados[14], //Actividad en el domicilio de desempeño,6
            'puesto_desem' => $datosProcesados[15], //Puesto desempeñado,4
            'rectificacion' => $datosProcesados[16], //Rectificación,1
            'numero_formulario_agro' => $datosProcesados[17], //Numero Formulario Agropecuario,10
            'tipo_servicio' => $datosProcesados[18], //Tipo de Servicio,3
            'categoria_profesional' => $datosProcesados[19], //Categoría Profesional,6
            'ccct' => $datosProcesados[20], //Código de Convenio Colectivo de Trabajo,7
            'no_hay_datos' => $datosProcesados[21], // campo vacio,5
        ];
        return $datosMapeados;
    }
}
