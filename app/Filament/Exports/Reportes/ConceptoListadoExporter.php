<?php

namespace App\Filament\Exports\Reportes;

use App\Models\Reportes\ConceptoListado;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ConceptoListadoExporter extends Exporter
{
    protected static ?string $model = ConceptoListado::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('codc_uacad')->label('Dep'),
            ExportColumn::make('periodo_fiscal')->label('Periodo'),
            ExportColumn::make('desc_liqui')->label('Liquidacion'),
            ExportColumn::make('nro_legaj')->label('legajo'),
            ExportColumn::make('cuil'),
            ExportColumn::make('apellido'),
            ExportColumn::make('nombre'),
            ExportColumn::make('oficina_pago')->label('Of. pago'),
            ExportColumn::make('codigoescalafon')->label('Escalafon'),
            ExportColumn::make('secuencia'),
            ExportColumn::make('categoria_completa')->label('Cargo'),
            ExportColumn::make('codn_conce')->label('Concepto'),
            ExportColumn::make('tipo_conce'),
            ExportColumn::make('impp_conce')->label('Importe'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your concepto listado export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getFileDisk(): string
    {
        return 'local';
    }
}
