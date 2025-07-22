<?php

namespace App\Repositories\Sicoss;

use App\Repositories\Sicoss\Contracts\SicossFormateadorRepositoryInterface;

class SicossFormateadorRepository implements SicossFormateadorRepositoryInterface
{
    /**
     * Formatear un valor numérico llenando con ceros a la izquierda.
     *
     * @param mixed $valor Valor a formatear
     * @param int $longitud Longitud total del campo
     *
     * @return string Valor formateado
     */
    public function llenaImportes($valor, int $longitud): string
    {
        if ($valor === null) {
            $valor = '';
        }

        if (\strlen(trim($valor)) > $longitud) {
            return substr($valor, -($longitud));
        }
        return str_pad($valor, $longitud, '0', \STR_PAD_LEFT);
    }

    /**
     * Formatear texto rellenando con espacios a la izquierda.
     *
     * @param string $texto Texto a formatear
     * @param int $longitud Longitud total del campo
     *
     * @return string Texto formateado
     */
    public function llenaBancosIzq(string $texto, int $longitud): string
    {
        if (\strlen(trim($texto)) > $longitud) {
            return substr($texto, -($longitud));
        }
        return str_pad($texto, $longitud, ' ', \STR_PAD_LEFT);
    }

    /**
     * Formatear texto rellenando con espacios a la derecha (conserva iniciales)
     * En los casos que se supera la longitud máxima con llenaBancosIzq se cortaban las iniciales en los agentes.
     *
     * @param string $texto Texto a formatear
     * @param int $longitud Longitud total del campo
     *
     * @return string Texto formateado
     */
    public function llenaBlancosModificado(string $texto, int $longitud): string
    {
        if (\strlen(trim($texto)) > $longitud) {
            return substr($texto, 0, $longitud);
        }
        return str_pad($texto, $longitud, ' ', \STR_PAD_RIGHT);
    }

    /**
     * Formatear texto rellenando con espacios a la derecha.
     *
     * @param string $texto Texto a formatear
     * @param int $longitud Longitud total del campo
     *
     * @return string Texto formateado
     */
    public function llenaBlancos(string $texto, int $longitud): string
    {
        if (\strlen(trim($texto)) > $longitud) {
            return substr($texto, -($longitud));
        }
        return str_pad($texto, $longitud, ' ', \STR_PAD_RIGHT);
    }

    /**
     * Transformar totales a formato recordset para reportes
     * Devuelve importes totales con formato adecuado para un cuadro toba.
     *
     * @param array $totalesPeriodo Array con totales por período
     *
     * @return array Recordset formateado
     */
    public function transformarARecordset(array $totalesPeriodo): array
    {
        $totales = [];
        $i = 0;

        foreach ($totalesPeriodo as $clave => $valor) {
            $totales[$i++] = ['variable' => 'BRUTO',        'valor' => $valor['bruto'], 'periodo' => $clave];
            $totales[$i++] = ['variable' => 'IMPONIBLE 1',  'valor' => $valor['imponible_1'], 'periodo' => $clave];
            $totales[$i++] = ['variable' => 'IMPONIBLE 2',  'valor' => $valor['imponible_2'], 'periodo' => $clave];
            $totales[$i++] = ['variable' => 'IMPONIBLE 3',  'valor' => $valor['imponible_2'], 'periodo' => $clave];
            $totales[$i++] = ['variable' => 'IMPONIBLE 4',  'valor' => $valor['imponible_4'], 'periodo' => $clave];
            $totales[$i++] = ['variable' => 'IMPONIBLE 5',  'valor' => $valor['imponible_5'], 'periodo' => $clave];
            $totales[$i++] = ['variable' => 'IMPONIBLE 6',  'valor' => $valor['imponible_6'], 'periodo' => $clave];
            $totales[$i++] = ['variable' => 'IMPONIBLE 7',  'valor' => $valor['imponible_6'], 'periodo' => $clave];
            $totales[$i++] = ['variable' => 'IMPONIBLE 8',  'valor' => $valor['imponible_8'], 'periodo' => $clave];
            $totales[$i++] = ['variable' => 'IMPONIBLE 9',  'valor' => $valor['imponible_9'], 'periodo' => $clave];
        }

        return $totales;
    }
}
