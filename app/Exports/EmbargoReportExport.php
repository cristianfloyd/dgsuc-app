<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProperties;

/**
 * Clase para exportar el reporte completo de embargos a Excel
 * Incluye múltiples hojas con diferentes vistas de los datos.
 */
class EmbargoReportExport implements WithMultipleSheets, WithProperties
{
    /**
     * @var Builder Consulta base para obtener los datos de embargos
     */
    protected Builder $query;

    /**
     * @var string Período de liquidación del reporte
     */
    protected string $periodoLiquidacion;

    /**
     * Constructor.
     *
     * @param Builder $query Consulta para obtener los datos
     * @param string $periodoLiquidacion Período de liquidación (opcional)
     */
    public function __construct(Builder $query, string $periodoLiquidacion = '')
    {
        $this->query = $query;
        $this->periodoLiquidacion = $periodoLiquidacion ?: date('Y-m');
    }

    /**
     * Define las hojas que componen el archivo Excel.
     *
     * @return array
     */
    public function sheets(): array
    {
        return [
            new EmbargoDetailSheet($this->query),
            new EmbargoSummarySheet($this->query),
            new EmbargoUacadSummary($this->query),
        ];
    }

    /**
     * Define las propiedades del documento Excel.
     *
     * @return array
     */
    public function properties(): array
    {
        return [
            'creator' => config('app.name'),
            'title' => "Reporte de Embargos - {$this->periodoLiquidacion}",
            'description' => 'Reporte detallado de embargos con información de conceptos adicionales',
            'company' => 'Universidad de Buenos Aires',
            'category' => 'Reportes Financieros',
            'manager' => 'Sistema de Informes',
            'created' => now(),
            'lastModifiedBy' => 'Sistema Automatizado',
            'subject' => "Embargos Período {$this->periodoLiquidacion}",
            'keywords' => 'embargos, liquidación, conceptos, remunerativo',
        ];
    }
}
