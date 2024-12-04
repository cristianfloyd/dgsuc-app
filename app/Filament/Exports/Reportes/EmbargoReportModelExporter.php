<?php

namespace App\Filament\Exports\Reportes;

use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use App\Models\Reportes\EmbargoReportModel;
use Filament\Actions\Exports\Models\Export;

class EmbargoReportModelExporter extends Exporter
{
    protected static ?string $model = EmbargoReportModel::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('nro_legaj')->label('Legajo'),
            ExportColumn::make('nro_cargo')->label('Cargo'),
            ExportColumn::make('nro_embargo')->label('Nro. Embargo'),
            ExportColumn::make('codc_uacad')->label('Unidad Acad'),
            ExportColumn::make('nombre_completo')->label('Nombre Completo'),
            ExportColumn::make('codn_conce')->label('Concepto'),
            ExportColumn::make('importe_descontado')->label('Importe'),
            ExportColumn::make('caratula')->label('Caratula'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your embargo report model export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
