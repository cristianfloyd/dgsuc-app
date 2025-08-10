<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SicossExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected Collection $registros;

    protected string $periodoFiscal;

    /**
     * Constructor.
     *
     * @param Collection $registros Registros a exportar
     * @param string|null $periodoFiscal Periodo fiscal (formato YYYYMM)
     */
    public function __construct(Collection $registros, ?string $periodoFiscal = null)
    {
        $this->registros = $registros;
        $this->periodoFiscal = $periodoFiscal ?? date('Ym');
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->registros;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'CUIL',
            'Apellido y Nombre',
            'Cónyuge',
            'Cant. Hijos',
            'Situación',
            'Condición',
            'Actividad',
            'Zona',
            'Porc. Aporte',
            'Mod. Contratación',
            'Obra Social',
            'Cant. Adherentes',
            'Rem. Total',
            'Rem. Imponible 1',
            'Asig. Fam. Pagadas',
            'Aporte Voluntario',
            'Imp. Adic. OS',
            'Exc. Aporte SS',
            'Exc. Aporte OS',
            'Provincia',
            'Rem. Imponible 2',
            'Rem. Imponible 3',
            'Rem. Imponible 4',
            'Siniestrado',
            'Reducción',
            'Recomp. LRT',
            'Tipo Empresa',
            'Aporte Adic. OS',
            'Régimen',
            'Sit. Revista 1',
            'Día Inicio SR1',
            'Sit. Revista 2',
            'Día Inicio SR2',
            'Sit. Revista 3',
            'Día Inicio SR3',
            'Sueldo Adicional',
            'SAC',
            'Horas Extras',
            'Zona Desfavorable',
            'Vacaciones',
            'Días Trabajados',
            'Rem. Imponible 5',
            'Convencionado',
            'Rem. Imponible 6',
            'Tipo Operación',
            'Adicionales',
            'Premios',
            'Rem. Dec. 788',
            'Rem. Imponible 7',
            'Nro. Horas Extras',
            'Conceptos No Remunerativos',
            'Maternidad',
            'Rectificación Remuneración',
            'Rem. Imponible 9',
            'Contribución Diferencial',
            'Horas Trabajadas',
            'Seguro',
            'Ley',
            'Inc. Salarial',
            'Rem. Imponible 11',
            'Diferencia Rem.',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->cuil,
            $row->apnom,
            $row->conyuge ? 'Sí' : 'No',
            $row->cant_hijos,
            $row->cod_situacion,
            $row->cod_cond,
            $row->cod_act,
            $row->cod_zona,
            $row->porc_aporte,
            $row->cod_mod_cont,
            $row->cod_os,
            $row->cant_adh,
            $row->rem_total,
            $row->rem_impo1,
            $row->asig_fam_pag,
            $row->aporte_vol,
            $row->imp_adic_os,
            $row->exc_aport_ss,
            $row->exc_aport_os,
            $row->prov,
            $row->rem_impo2,
            $row->rem_impo3,
            $row->rem_impo4,
            $row->cod_siniestrado,
            $row->marca_reduccion ? 'Sí' : 'No',
            $row->recomp_lrt,
            $row->tipo_empresa,
            $row->aporte_adic_os,
            $row->regimen,
            $row->sit_rev1,
            $row->dia_ini_sit_rev1,
            $row->sit_rev2,
            $row->dia_ini_sit_rev2,
            $row->sit_rev3,
            $row->dia_ini_sit_rev3,
            $row->sueldo_adicc,
            $row->sac,
            $row->horas_extras,
            $row->zona_desfav,
            $row->vacaciones,
            $row->cant_dias_trab,
            $row->rem_impo5,
            $row->convencionado ? 'Sí' : 'No',
            $row->rem_impo6,
            $row->tipo_oper,
            $row->adicionales,
            $row->premios,
            $row->rem_dec_788,
            $row->rem_imp7,
            $row->nro_horas_ext,
            $row->cpto_no_remun,
            $row->maternidad,
            $row->rectificacion_remun,
            $row->rem_imp9,
            $row->contrib_dif,
            $row->hstrab,
            $row->seguro ? 'Sí' : 'No',
            $row->ley,
            $row->incsalarial,
            $row->remimp11,
            $row->diferencia_rem,
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'SICOSS ' . $this->periodoFiscal;
    }

    /**
     * @param Worksheet $sheet
     *
     * @return array
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            // Estilo para la primera fila (encabezados)
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA'],
                ],
            ],
            // Estilo para las columnas monetarias
            'M:N' => ['numberFormat' => ['formatCode' => '#,##0.00']],
            'P:S' => ['numberFormat' => ['formatCode' => '#,##0.00']],
            'U:W' => ['numberFormat' => ['formatCode' => '#,##0.00']],
            'Y:Y' => ['numberFormat' => ['formatCode' => '#,##0.00']],
            'AJ:AN' => ['numberFormat' => ['formatCode' => '#,##0.00']],
            'AP:AQ' => ['numberFormat' => ['formatCode' => '#,##0.00']],
            'AS:AW' => ['numberFormat' => ['formatCode' => '#,##0.00']],
            'AY:BC' => ['numberFormat' => ['formatCode' => '#,##0.00']],
            'BF:BH' => ['numberFormat' => ['formatCode' => '#,##0.00']],
            'BJ:BJ' => ['numberFormat' => ['formatCode' => '#,##0.00']],
        ];
    }
}
