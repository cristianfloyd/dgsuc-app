<?php

namespace App\Services;

use App\Exports\SicossExport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SicossExportService
{
    /**
     * Genera un archivo TXT con formato SICOSS a partir de una colección de registros.
     *
     * @param Collection $registros Registros a incluir en el archivo
     * @param string|null $periodoFiscal Periodo fiscal para el nombre del archivo (formato YYYYMM)
     *
     * @return string Ruta completa del archivo generado
     */
    public function generarArchivoTxt(Collection $registros, ?string $periodoFiscal = null): string
    {
        $contenido = '';
        $totalRegistros = $registros->count();
        $procesados = 0;

        Log::info("Iniciando exportación de archivo SICOSS TXT con {$totalRegistros} registros");

        foreach ($registros as $registro) {
            $linea = $this->generarLinea($registro);
            $contenido .= $linea . \PHP_EOL;
            $procesados++;

            // Loguear progreso cada 100 registros
            if ($procesados % 100 === 0) {
                Log::info("Exportación SICOSS TXT: {$procesados}/{$totalRegistros} registros procesados");
            }
        }

        // Usar el periodo fiscal proporcionado o el actual
        $periodoFiscal ??= date('Ym');
        $nombreArchivo = 'SICOSS_' . $periodoFiscal . '.txt';

        Storage::disk('local')->put('tmp/' . $nombreArchivo, $contenido);
        Log::info("Exportación SICOSS TXT completada: {$procesados} registros exportados a {$nombreArchivo}");

        return storage_path('app/tmp/' . $nombreArchivo);
    }

    /**
     * Genera un archivo Excel con los datos de SICOSS.
     *
     * @param Collection $registros Registros a incluir en el archivo
     * @param string|null $periodoFiscal Periodo fiscal para el nombre del archivo (formato YYYYMM)
     *
     * @return string Ruta completa del archivo generado
     */
    public function generarArchivoExcel(Collection $registros, ?string $periodoFiscal = null): string
    {
        // Usar el periodo fiscal proporcionado o el actual
        $periodoFiscal ??= date('Ym');
        $nombreArchivo = 'SICOSS_' . $periodoFiscal . '.xlsx';
        $rutaArchivo = storage_path('app/tmp/' . $nombreArchivo);

        // Si se implementa una clase de exportación específica para Excel
        // Excel::store(new SicossExport($registros), 'tmp/' . $nombreArchivo, 'local');

        // Implementación simple sin clase de exportación específica
        Excel::store(
            view('exports.sicoss', ['registros' => $registros]),
            'tmp/' . $nombreArchivo,
            'local',
        );

        return $rutaArchivo;
    }

    /**
     * Método genérico para generar un archivo en el formato especificado.
     *
     * @param Collection $registros Registros a incluir en el archivo
     * @param string $formato Formato del archivo ('txt' o 'excel')
     * @param string|null $periodoFiscal Periodo fiscal para el nombre del archivo
     *
     * @return string Ruta completa del archivo generado
     */
    public function generarArchivo(Collection $registros, string $formato = 'txt', ?string $periodoFiscal = null): string
    {
        return match (strtolower($formato)) {
            'excel', 'xlsx' => $this->generarArchivoExcel($registros, $periodoFiscal),
            'txt', 'text' => $this->generarArchivoTxt($registros, $periodoFiscal),
            default => $this->generarArchivoTxt($registros, $periodoFiscal),
        };
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
     *
     * @return string La línea de texto formateada para el archivo de exportación.
     */
    private function generarLinea($registro): string
    {
        $linea = '';

        // Datos de identificación personal
        $linea .= str_pad($registro->cuil ?? '0', 11, '0', \STR_PAD_LEFT);
        $linea .= $this->formatearString($registro->apnom, 30);

        // Datos familiares
        $linea .= $registro->conyuge ? '1' : '0';
        $linea .= str_pad($registro->cant_hijos ?? '0', 2, '0', \STR_PAD_LEFT);

        // Datos situación laboral
        $linea .= str_pad($registro->cod_situacion ?? '0', 2, '0', \STR_PAD_LEFT);
        $linea .= str_pad($registro->cod_cond ?? '0', 2, '0', \STR_PAD_LEFT);
        $linea .= str_pad($registro->cod_act ?? '0', 3, '0', \STR_PAD_LEFT);
        $linea .= str_pad($registro->cod_zona ?? '0', 2, '0', \STR_PAD_LEFT);

        // Datos aportes y obra social
        $linea .= $this->formatearDecimal($registro->porc_aporte, 5);
        $linea .= str_pad($registro->cod_mod_cont ?? '0', 3, '0', \STR_PAD_LEFT);
        $linea .= str_pad($registro->cod_os ?? '0', 6, '0', \STR_PAD_LEFT);
        $linea .= str_pad($registro->cant_adh ?? '0', 2, '0', \STR_PAD_LEFT);

        // Remuneraciones principales
        $linea .= $this->formatearDecimal($registro->rem_total, 12);
        $linea .= $this->formatearDecimal($registro->rem_impo1, 12);
        $linea .= $this->formatearDecimal($registro->asig_fam_pag, 9);
        $linea .= $this->formatearDecimal($registro->aporte_vol, 9);
        $linea .= $this->formatearDecimal($registro->imp_adic_os, 9);
        $linea .= $this->formatearDecimal($registro->exc_aport_ss, 9);
        $linea .= $this->formatearDecimal($registro->exc_aport_os, 9);
        $linea .= str_pad($registro->prov ?? 'CABA', 50, ' ', \STR_PAD_RIGHT);

        // Remuneraciones adicionales
        $linea .= $this->formatearDecimal($registro->rem_impo2, 12);
        $linea .= $this->formatearDecimal($registro->rem_impo3, 12);
        $linea .= $this->formatearDecimal($registro->rem_impo4, 12);

        // Datos siniestros y tipo empresa
        $linea .= str_pad($registro->cod_siniestrado ?? '0', 2, '0', \STR_PAD_LEFT);
        $linea .= $registro->marca_reduccion ? '1' : '0';
        $linea .= $this->formatearDecimal($registro->recomp_lrt, 9);
        $linea .= $registro->tipo_empresa ?? '0';
        $linea .= $this->formatearDecimal($registro->aporte_adic_os, 9);
        $linea .= $registro->regimen ?? '0';

        // Situaciones de revista
        $linea .= str_pad($registro->sit_rev1 ?? '0', 2, '0', \STR_PAD_LEFT);
        $linea .= str_pad($registro->dia_ini_sit_rev1 ?? '0', 2, '0', \STR_PAD_LEFT);
        $linea .= str_pad($registro->sit_rev2 ?? '0', 2, '0', \STR_PAD_LEFT);
        $linea .= str_pad($registro->dia_ini_sit_rev2 ?? '0', 2, '0', \STR_PAD_LEFT);
        $linea .= str_pad($registro->sit_rev3 ?? '0', 2, '0', \STR_PAD_LEFT);
        $linea .= str_pad($registro->dia_ini_sit_rev3 ?? '0', 2, '0', \STR_PAD_LEFT);

        // Conceptos salariales
        $linea .= $this->formatearDecimal($registro->sueldo_adicc, 12);
        $linea .= $this->formatearDecimal($registro->sac, 12);
        $linea .= $this->formatearDecimal($registro->horas_extras, 12);
        $linea .= $this->formatearDecimal($registro->zona_desfav, 12);
        $linea .= $this->formatearDecimal($registro->vacaciones, 12);

        // Datos laborales
        $linea .= str_pad($registro->cant_dias_trab ?? '0', 9, '0', \STR_PAD_LEFT);
        $linea .= $this->formatearDecimal($registro->rem_impo5, 12);
        $linea .= $registro->convencionado ? '1' : '0';
        $linea .= $this->formatearDecimal($registro->rem_impo6, 12);
        $linea .= $registro->tipo_oper ?? '0';

        // Conceptos adicionales
        $linea .= $this->formatearDecimal($registro->adicionales, 12);
        $linea .= $this->formatearDecimal($registro->premios, 12);
        $linea .= $this->formatearDecimal($registro->rem_dec_788, 12);
        $linea .= $this->formatearDecimal($registro->rem_imp7, 12);
        $linea .= str_pad($registro->nro_horas_ext ?? '0', 3, '0', \STR_PAD_LEFT);
        $linea .= $this->formatearDecimal($registro->cpto_no_remun, 12);

        // Conceptos especiales
        $linea .= $this->formatearDecimal($registro->maternidad, 12);
        $linea .= $this->formatearDecimal($registro->rectificacion_remun, 9);
        $linea .= $this->formatearDecimal($registro->rem_imp9, 12);
        $linea .= $this->formatearDecimal($registro->contrib_dif, 9);

        // Datos finales
        $linea .= $this->formatearNumero($registro->hstrab, 3);
        $linea .= $registro->seguro ? 'T' : 'F';
        $linea .= $this->formatearDecimal($registro->ley, 12);
        $linea .= $this->formatearDecimal($registro->incsalarial, 12);
        $linea .= $this->formatearDecimal($registro->remimp11, 12);

        // Asegurar longitud exacta de 500 caracteres
        return $this->ajustarLongitud($linea, 500);
    }

    private function formatearDecimal(?float $valor, int $longitud): string
    {
        $valor ??= 0;
        $numeroFormateado = number_format($valor, 2, '.', '');
        return str_pad($numeroFormateado, $longitud, '0', \STR_PAD_LEFT);
    }

    /**
     * Formatea un string con longitud fija, manejando correctamente caracteres especiales.
     *
     * @param string|null $valor El valor a formatear
     * @param int $longitud La longitud deseada del string resultante
     *
     * @return string El valor formateado
     */
    private function formatearString(?string $valor, int $longitud): string
    {
        // Asegurar que no sea null
        $valor ??= '';

        // Convertir a ISO-8859-1 (Latin1) para manejar acentos
        $valorLatin1 = mb_convert_encoding($valor, 'ISO-8859-1', 'UTF-8');

        // Calcular la longitud real en bytes (importante para caracteres especiales)
        $longitudActual = \strlen($valorLatin1);

        // Truncar si es necesario
        if ($longitudActual > $longitud) {
            $valorLatin1 = substr($valorLatin1, 0, $longitud);
        }

        // Calcular cuántos espacios necesitamos añadir
        $espaciosNecesarios = $longitud - \strlen($valorLatin1);

        // Añadir espacios al final para alcanzar la longitud exacta
        if ($espaciosNecesarios > 0) {
            $valorLatin1 .= str_repeat(' ', $espaciosNecesarios);
        }

        // Verificar la longitud final
        if (\strlen($valorLatin1) !== $longitud) {
            // Log de error o ajuste adicional si es necesario
            $valorLatin1 = str_pad(substr($valorLatin1, 0, $longitud), $longitud, ' ', \STR_PAD_RIGHT);
        }

        return $valorLatin1;
    }

    /**
     * Formatea un número como string con longitud fija, rellenando con ceros a la izquierda.
     * Si el valor es mayor que la longitud especificada, se trunca.
     *
     * @param mixed $valor El valor a formatear
     * @param int $longitud La longitud deseada del string resultante
     *
     * @return string El valor formateado
     */
    private function formatearNumero($valor, int $longitud): string
    {
        // Convertir a string y asegurar que no sea null
        $valorString = (string)($valor ?? '0');

        // Si el valor es más largo que la longitud deseada, truncarlo
        if (\strlen($valorString) > $longitud) {
            $valorString = substr($valorString, 0, $longitud);
        }

        // Rellenar con ceros a la izquierda hasta alcanzar la longitud deseada
        return str_pad($valorString, $longitud, '0', \STR_PAD_LEFT);
    }

    private function ajustarLongitud(string $linea, int $longitud): string
    {
        if (\strlen($linea) > $longitud) {
            return substr($linea, 0, $longitud);
        }
        return str_pad($linea, $longitud, ' ', \STR_PAD_RIGHT);
    }
}
