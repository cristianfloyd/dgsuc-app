<?php

namespace App\Repositories\Sicoss;

use App\Models\Mapuche\MapucheConfig;
use App\Repositories\Sicoss\Contracts\SicossEstadoRepositoryInterface;

class SicossEstadoRepository implements SicossEstadoRepositoryInterface
{
    /**
     * Inicializa el estado de situación para un rango de días.
     *
     * @param int $codigo
     * @param int $min
     * @param int $max
     *
     * @return array
     */
    public function inicializarEstadoSituacion(int $codigo, int $min, int $max): array
    {
        $periodo = MapucheConfig::getPeriodoFiscal();
        $estado_situacion = [];
        for ($i = $min; $i <= $max; $i++) {
            $estado_situacion[$i] = $codigo;
        }
        return $estado_situacion;
    }

    /**
     * Se le pasa la condición actual y se compara con la condición
     * obtenida a partir del tipo de licencia (5 => maternidad o 13 => no remunerada o 18/19 => ILT).
     *
     * @param int $c1 condición actual
     * @param int $c2 condición tipo de licencia
     *
     * @return int
     */
    public function evaluarCondicionLicencia(int $c1, int $c2): int
    {
        // Maternidad primero
        if ($c1 == 5 || $c2 == 5) {
            return 5;
        }
        if ($c1 == 11 || $c2 == 11) {
            return 11;
        }
        if ($c1 == 10 || $c2 == 10) {
            return 10;
        }
        if ($c1 == 18 || $c2 == 18) {
            return 18;
        }
        if ($c1 == 19 || $c2 == 19) {
            return 19;
        }
        if ($c1 == 13 || $c2 == 13) {
            return 13;
        }
        if ($c1 == 12 || $c2 == 12) {
            return 12;
        }
        if ($c1 == 51 || $c2 == 51) {
            return 51;
        }

        return $c1; // Por defecto se retorna la condición actual
    }

    /**
     * Calcula los cambios de estado en el período.
     *
     * @param array $estado_situacion
     *
     * @return array
     */
    public function calcularCambiosEstado(array $estado_situacion): array
    {
        $cambios = [];
        $anterior = null;

        foreach ($estado_situacion as $dia => $codigo) {
            if (!isset($anterior) || $anterior != $codigo) {
                $cambios[$dia] = $codigo;
            }
            $anterior = $codigo;
        }

        return $cambios;
    }

    /**
     * Calcula los días trabajados según códigos de situación.
     *
     * @param array $estado_situacion
     *
     * @return int
     */
    public function calcularDiasTrabajados(array $estado_situacion): int
    {
        $dias_trabajados = 0;
        foreach ($estado_situacion as $codigo) {
            // Se suman solo los días trabajados, código 1
            // Los días de Licencia por Maternidad también cuentan como trabajados
            if ($codigo === 1 || $codigo === 5 || $codigo === 12 || $codigo === 51) {
                $dias_trabajados += 1;
            }
        }

        return $dias_trabajados;
    }

    /**
     * Calcula la revista del legajo basada en los cambios de estado.
     *
     * @param array $cambios_estado
     *
     * @return array
     */
    public function calcularRevistaLegajo(array $cambios_estado): array
    {
        $controlar_maternidad = false;
        $revista_legajo = [];
        $cantidad_cambios = \count($cambios_estado);
        $dias = array_keys($cambios_estado);

        $revista_legajo[1] = ['codigo' => 0, 'dia' => 0];
        $revista_legajo[2] = ['codigo' => 0, 'dia' => 0];
        $revista_legajo[3] = ['codigo' => 0, 'dia' => 0];

        $primer_dia = 0;

        if ($cantidad_cambios > 3) {
            $primer_dia = $cantidad_cambios - 3;
            $controlar_maternidad = true;
        }

        $revista = 1;
        for ($i = $primer_dia; $i < $cantidad_cambios; $i++) {
            $dia = $dias[$i];
            $revista_legajo[$revista] = ['codigo' => $cambios_estado[$dia], 'dia' => $dia];
            $revista++;
        }

        if ($controlar_maternidad) {
            $dia_revista = $revista_legajo[1]['dia'];
            foreach ($cambios_estado as $dia => $situacion) {
                if (($situacion == 5) && ($dia < $dia_revista)) {
                    $revista_legajo[1]['dia'] = $dia;
                    $revista_legajo[1]['codigo'] = $situacion;
                }
            }
        }

        return $revista_legajo;
    }

    /**
     * Verifica los importes dado un legajo, si todos son ceros entonces no debe tenerse en cuenta en el informe sicoss.
     *
     * @param array $leg
     *
     * @return int
     */
    public function verificarAgenteImportesCero(array $leg): int
    {
        $VerificarAgenteImportesCERO = 1;
        if ($leg['IMPORTE_BRUTO'] == 0 && $leg['IMPORTE_IMPON'] == 0 && $leg['AsignacionesFliaresPagadas'] == 0 && $leg['ImporteNoRemun'] == 0 && $leg['IMPORTE_ADICI'] == 0 && $leg['IMPORTE_VOLUN'] == 0) {
            $VerificarAgenteImportesCERO = 0;
        }
        return $VerificarAgenteImportesCERO;
    }
}
