<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class SicossExportService
{
    public function generarArchivo(Collection $registros): string
    {
        $contenido = '';

        foreach ($registros as $registro) {
            $linea = $this->generarLinea($registro);
            $contenido .= $linea . PHP_EOL;
        }

        $nombreArchivo = 'SICOSS_' . date('Ym') . '.txt';
        Storage::disk('local')->put('tmp/' . $nombreArchivo, $contenido);
        return storage_path('app/tmp/' . $nombreArchivo);
    }

    /**
     * Genera una línea de texto para el archivo de exportación basada en los datos del registro.
     *
     * Esta función toma un registro como parámetro y devuelve una cadena de texto formateada
     * según los requisitos específicos de la exportación SICOSS. La cadena de texto incluye
     * información personal, familiar, laboral, de aportes y obra social, remuneraciones, datos
     * siniestros y tipo de empresa, situaciones de revista, conceptos salariales, datos laborales,
     * conceptos adicionales, conceptos especiales y datos finales.
     *
     * @param mixed $registro El registro que se utilizará para generar la línea de texto.
     * @return string La línea de texto formateada para el archivo de exportación.
     */
    private function generarLinea($registro): string
    {
        $linea = '';

        // Datos de identificación personal
        $linea .= str_pad($registro->cuil ?? '0', 11, '0', STR_PAD_LEFT);
        $linea .= $this->formatearString($registro->apnom, 30);;

        // Datos familiares
        $linea .= $registro->conyuge ? '1' : '0';
        $linea .= str_pad($registro->cant_hijos ?? '0', 2, '0', STR_PAD_LEFT);

        // Datos situación laboral
        $linea .= str_pad($registro->cod_situacion ?? '0', 2, '0', STR_PAD_LEFT);
        $linea .= str_pad($registro->cod_cond ?? '0', 2, '0', STR_PAD_LEFT);
        $linea .= str_pad($registro->cod_act ?? '0', 3, '0', STR_PAD_LEFT);
        $linea .= str_pad($registro->cod_zona ?? '0', 2, '0', STR_PAD_LEFT);

        // Datos aportes y obra social

        $linea .= $this->formatearDecimal($registro->porc_aporte, 5);
        $linea .= str_pad($registro->cod_mod_cont ?? '0', 3, '0', STR_PAD_LEFT);
        $linea .= str_pad($registro->cod_os ?? '0', 6, '0', STR_PAD_LEFT);
        $linea .= str_pad($registro->cant_adh ?? '0', 2, '0', STR_PAD_LEFT);

        // Remuneraciones principales
        $linea .= $this->formatearDecimal($registro->rem_total, 12);
        $linea .= $this->formatearDecimal($registro->rem_impo1, 12);
        $linea .= $this->formatearDecimal($registro->asig_fam_pag, 9);
        $linea .= $this->formatearDecimal($registro->aporte_vol, 9);
        $linea .= $this->formatearDecimal($registro->imp_adic_os, 9);
        $linea .= $this->formatearDecimal($registro->exc_aport_ss, 9);
        $linea .= $this->formatearDecimal($registro->exc_aport_os, 9);
        $linea .= str_pad($registro->prov ?? 'CABA', 50, ' ', STR_PAD_RIGHT);

        // Remuneraciones adicionales
        $linea .= $this->formatearDecimal($registro->rem_impo2, 12);
        $linea .= $this->formatearDecimal($registro->rem_impo3, 12);
        $linea .= $this->formatearDecimal($registro->rem_impo4, 12);

        // Datos siniestros y tipo empresa
        $linea .= str_pad($registro->cod_siniestrado ?? '0', 2, '0', STR_PAD_LEFT);
        $linea .= $registro->marca_reduccion ? '1' : '0';
        $linea .= $this->formatearDecimal($registro->recomp_lrt, 9);
        $linea .= $registro->tipo_empresa ?? '0';
        $linea .= $this->formatearDecimal($registro->aporte_adic_os, 9);
        $linea .= $registro->regimen ?? '0';

        // Situaciones de revista
        $linea .= str_pad($registro->sit_rev1 ?? '0', 2, '0', STR_PAD_LEFT);
        $linea .= str_pad($registro->dia_ini_sit_rev1 ?? '0', 2, '0', STR_PAD_LEFT);
        $linea .= str_pad($registro->sit_rev2 ?? '0', 2, '0', STR_PAD_LEFT);
        $linea .= str_pad($registro->dia_ini_sit_rev2 ?? '0', 2, '0', STR_PAD_LEFT);
        $linea .= str_pad($registro->sit_rev3 ?? '0', 2, '0', STR_PAD_LEFT);
        $linea .= str_pad($registro->dia_ini_sit_rev3 ?? '0', 2, '0', STR_PAD_LEFT);

        // Conceptos salariales
        $linea .= $this->formatearDecimal($registro->sueldo_adicc, 12);
        $linea .= $this->formatearDecimal($registro->sac, 12);
        $linea .= $this->formatearDecimal($registro->horas_extras, 12);
        $linea .= $this->formatearDecimal($registro->zona_desfav, 12);
        $linea .= $this->formatearDecimal($registro->vacaciones, 12);

        // Datos laborales
        $linea .= str_pad($registro->cant_dias_trab ?? '0', 9, '0', STR_PAD_LEFT);
        $linea .= $this->formatearDecimal($registro->rem_impo5, 12);
        $linea .= $registro->convencionado ? '1' : '0';
        $linea .= $this->formatearDecimal($registro->rem_impo6, 12);
        $linea .= $registro->tipo_oper ?? '0';

        // Conceptos adicionales
        $linea .= $this->formatearDecimal($registro->adicionales, 12);
        $linea .= $this->formatearDecimal($registro->premios, 12);
        $linea .= $this->formatearDecimal($registro->rem_dec_788_05, 12);
        $linea .= $this->formatearDecimal($registro->rem_imp7, 12);
        $linea .= str_pad($registro->nro_horas_ext ?? '0', 3, '0', STR_PAD_LEFT);
        $linea .= $this->formatearDecimal($registro->cpto_no_remun, 12);

        // Conceptos especiales
        $linea .= $this->formatearDecimal($registro->maternidad, 12);
        $linea .= $this->formatearDecimal($registro->rectificacion_remun, 9);
        $linea .= $this->formatearDecimal($registro->rem_imp9, 12);
        $linea .= $this->formatearDecimal($registro->contrib_dif, 9);

        // Datos finales
        $linea .= str_pad($registro->hstrab ?? '0', 3, '0', STR_PAD_LEFT);
        $linea .= $registro->seguro ? '1' : '0';
        $linea .= $this->formatearDecimal($registro->ley_27430, 12);
        $linea .= $this->formatearDecimal($registro->incsalarial, 12);
        $linea .= $this->formatearDecimal($registro->remimp11, 12);

        // Asegurar longitud exacta de 500 caracteres
        return $this->ajustarLongitud($linea, 500);
    }

    private function formatearDecimal(?float $valor, int $longitud): string
    {
        $valor = $valor ?? 0;
        $numeroFormateado = number_format($valor, 2, '.', '');
        return str_pad($numeroFormateado, $longitud, '0', STR_PAD_LEFT);
    }

    private function formatearString(?string $valor, int $longitud): string
    {
        $valor = $valor ?? '';
        $valor = mb_convert_encoding($valor, 'ISO-8859-1', 'UTF-8');
        $valor = substr($valor, 0, $longitud);
        return str_pad($valor, $longitud, ' ', STR_PAD_RIGHT);
    }

    private function formatearNumero($valor, int $longitud): string
    {
        return str_pad($valor ?? '0', $longitud, '0', STR_PAD_LEFT);
    }

    private function ajustarLongitud(string $linea, int $longitud): string
    {
        if (strlen($linea) > $longitud) {
            return substr($linea, 0, $longitud);
        }
        return str_pad($linea, $longitud, ' ', STR_PAD_RIGHT);
    }
}
