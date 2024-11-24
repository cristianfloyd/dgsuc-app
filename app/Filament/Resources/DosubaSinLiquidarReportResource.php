<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Mapuche\Dh22;
use App\Models\Mapuche\Dh21h;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use App\Services\Mapuche\Dh22Service;
use App\Models\DosubaSinLiquidarReport;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DosubaSinLiquidarReportResource\Pages;

class DosubaSinLiquidarReportResource extends Resource
{
    protected static ?string $model = Dh21h::class;
    protected static ?string $modelLabel = 'Reporte de Legajos Sin Liquidar';
    protected static ?string $pluralModelLabel = 'Reportes de Legajos Sin Liquidar';
    protected static ?string $navigationLabel = 'Legajos Sin Liquidar';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getUltimasTresLiquidacionesDefinitivas()
    {
        return Dh22::query()
            ->definitiva()
            ->select('nro_liqui', 'desc_liqui', 'per_liano', 'per_limes')
            ->orderBy('per_liano', 'desc')
            ->orderBy('per_limes', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($d) => [
                'nro_liqui' => $d->nro_liqui,
                'per_liano' => $d->per_liano,
                'per_limes' => $d->per_limes,
                'desc_liqui' => $d->desc_liqui,
                'periodoFiscal' => $d->periodoFiscal
                ]);
    }

    public static function table(Table $table): Table
    {
        $ultimasLiquidaciones = (new static)->getUltimasTresLiquidacionesDefinitivas();
        $periodosLiquidacion = $ultimasLiquidaciones->pluck('periodoFiscal')->toArray();

        return $table
            ->columns([
                TextColumn::make('nro_legaj')
                    ->label('Legajo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nro_cargo')
                    ->label('Cargo')
                    ->sortable(),
                TextColumn::make('codc_uacad')
                    ->label('Unidad Académica')
                    ->sortable(),
                TextColumn::make('dh22.periodoFiscal')
                    ->label('Último Período Liquidado')
                    ->formatStateUsing(function ($state) use ($periodosLiquidacion) {
                        try {
                            return in_array($state, $periodosLiquidacion)
                                ? $state
                                : 'Sin Liquidación';
                        } catch (\Exception $e) {
                            return 'Error al procesar período';
                        }
                    })
                    ->tooltip(function ($state) {
                        return "Período: $state";
                    }),
                TextColumn::make('dh22.desc_liqui')
                    ->label('Descripción Liquidación')
                    ->sortable()

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('codc_uacad')
                    ->label('Unidad Académica')
                    ->options(
                        Dh21h::distinct()
                            ->pluck('codc_uacad', 'codc_uacad')
                            ->toArray()
                    ),
                Tables\Filters\SelectFilter::make('nro_liqui')
                    ->label('Liquidación')
                    ->options(function() {
                        return app(Dh22Service::class)->getLiquidacionesParaSelect();
                    }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDosubaSinLiquidarReports::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('dh22')
            ->select(['dh21h.*'])
            ->orderBy('nro_liqui', 'desc');
    }
}
