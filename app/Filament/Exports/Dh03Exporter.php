<?php

namespace App\Filament\Exports;

use App\Models\Dh03;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class Dh03Exporter extends Exporter
{
    protected static ?string $model = Dh03::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('nro_cargo'),
            ExportColumn::make('rama'),
            ExportColumn::make('disciplina'),
            ExportColumn::make('area'),
            ExportColumn::make('porcdedicdocente'),
            ExportColumn::make('porcdedicinvestig'),
            ExportColumn::make('porcdedicagestion'),
            ExportColumn::make('porcdedicaextens'),
            ExportColumn::make('codigocontrato'),
            ExportColumn::make('horassemanales'),
            ExportColumn::make('duracioncontrato'),
            ExportColumn::make('incisoimputacion'),
            ExportColumn::make('montocontrato'),
            ExportColumn::make('nro_legaj'),
            ExportColumn::make('fec_alta'),
            ExportColumn::make('fec_baja'),
            ExportColumn::make('codc_carac'),
            ExportColumn::make('codc_categ'),
            ExportColumn::make('codc_agrup'),
            ExportColumn::make('tipo_norma'),
            ExportColumn::make('tipo_emite'),
            ExportColumn::make('fec_norma'),
            ExportColumn::make('nro_norma'),
            ExportColumn::make('codc_secex'),
            ExportColumn::make('nro_exped'),
            ExportColumn::make('nro_exped_baja'),
            ExportColumn::make('fec_exped_baja'),
            ExportColumn::make('ano_exped_baja'),
            ExportColumn::make('codc_secex_baja'),
            ExportColumn::make('ano_exped'),
            ExportColumn::make('fec_exped'),
            ExportColumn::make('nro_tab13'),
            ExportColumn::make('codc_uacad'),
            ExportColumn::make('nro_tab18'),
            ExportColumn::make('codc_regio'),
            ExportColumn::make('codc_grado'),
            ExportColumn::make('vig_caano'),
            ExportColumn::make('vig_cames'),
            ExportColumn::make('chk_proye'),
            ExportColumn::make('tipo_incen'),
            ExportColumn::make('dedi_incen'),
            ExportColumn::make('cic_con'),
            ExportColumn::make('fec_limite'),
            ExportColumn::make('porc_aplic'),
            ExportColumn::make('hs_dedic'),
            ExportColumn::make('tipo_norma_baja'),
            ExportColumn::make('tipoemitenormabaja'),
            ExportColumn::make('fecha_norma_baja'),
            ExportColumn::make('fechanotificacion'),
            ExportColumn::make('coddependesemp'),
            ExportColumn::make('chkfirmaencargado'),
            ExportColumn::make('chkfirmaautoridad'),
            ExportColumn::make('chkestadoafip'),
            ExportColumn::make('chkestadotitulo'),
            ExportColumn::make('chkestadocv'),
            ExportColumn::make('objetocontrato'),
            ExportColumn::make('nro_norma_baja'),
            ExportColumn::make('fechagrado'),
            ExportColumn::make('fechapermanencia'),
            ExportColumn::make('fecaltadesig'),
            ExportColumn::make('fecbajadesig'),
            ExportColumn::make('motivoaltadesig'),
            ExportColumn::make('motivobajadesig'),
            ExportColumn::make('renovacion'),
            ExportColumn::make('idtareacargo'),
            ExportColumn::make('chktrayectoria'),
            ExportColumn::make('chkfuncionejec'),
            ExportColumn::make('chkretroactivo'),
            ExportColumn::make('chkstopliq'),
            ExportColumn::make('nro_cargo_ant'),
            ExportColumn::make('transito'),
            ExportColumn::make('cod_clasif_cargo'),
            ExportColumn::make('cod_licencia'),
            ExportColumn::make('cargo_concursado'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your dh03 export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
