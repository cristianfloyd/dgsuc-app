<?php

namespace App\Filament\Reportes\Resources;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Mapuche\Embargo;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Services\Reportes\EmbargoReportService;
use App\Filament\Reportes\Resources\EmbargoResource\Pages\ListEmbargos;
use App\Filament\Reportes\Resources\EmbargoResource\Pages\EmbargoReport;

class EmbargoResource extends Resource
{
    protected static ?string $model = Embargo::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Reportes';



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_embargo')->label('id')->sortable()->searchable(),
                TextColumn::make('nro_legaj')->sortable()->searchable(),
                TextColumn::make('datosPersonales.nombre_completo')
                    ->label('Nombre Completo')
                    ->limit(10)
                    ->tooltip(fn (TextColumn $column): string => $column->getState())
                    ->sortable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $searchTerm = strtoupper($search);
                        return $query->whereHas('datosPersonales', function ($query) use ($searchTerm) {
                            $query->where('desc_appat', 'like', "%{$searchTerm}%")
                                  ->orWhere('desc_apmat', 'like', "%{$searchTerm}%")
                                  ->orWhere('desc_apcas', 'like', "%{$searchTerm}%")
                                  ->orWhere('desc_nombr', 'like', "%{$searchTerm}%");
                        });
                    }),
                TextColumn::make('imp_embargo')
                    ->money('ARS')
                    ->label('Importe Total')
                    ->sortable(),
                TextColumn::make('importe_descontado')
                    ->money('ARS')
                    ->label('Importe Descontado')
                    ->state(function (Embargo $record): float {
                        $importes = $record->getImporteDescontado(3);
                        return $importes->sum('impp_conce');
                    })
                    ->tooltip(function (Embargo $record): string {
                        $importes = $record->getImporteDescontado(3);
                        return $importes->map(function ($importe) {
                            return "Cargo {$importe->nro_cargo}: $ " . number_format($importe->impp_conce, 2);
                        })->join("\n");
                    })
                    ->sortable(),
                TextColumn::make('tipoEmbargo.codn_conce')->label('concepto')->sortable(),
                TextColumn::make('saldo_pendiente')
                    ->money('ARS')
                    ->label('Saldo Pendiente')
                    ->state(function (Embargo $record): float {
                        $importes = $record->getImporteDescontado(3);
                        return $record->imp_embargo - $importes->sum('impp_conce');
                    })
                    ,
                TextColumn::make('estado.desc_estado_embargo')->label('estado')->sortable(),
                TextColumn::make('beneficiario.nom_beneficiario')->label('beneficiario')->sortable(),
                TextColumn::make('juzgado.nom_juzgado')->sortable()
                    ->limit(15)
                    ->tooltip(fn(TextColumn $column): string => $column->getState()),
                TextColumn::make('tipoEmbargo.desc_tipo_embargo')->sortable()
                    ->limit(10)
                    ->tooltip(fn(TextColumn $column): string => $column->getState()),
                TextColumn::make('tipoJuicio.desc_tipo_juicio')->label('Tipo Juicio'),
                TextColumn::make('imp_embargo')
                    ->money('ARS')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fec_inicio')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('caratula')->sortable(),
            ])
            ->headerActions([
                //
            ])
            ->filters([
                SelectFilter::make('id_estado_embargo')
                    ->relationship('estado', 'desc_estado_embargo')
                    ->label('Estado')
                    ->default(2),
            ])
            ->actions([
                //
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
            'index' => ListEmbargos::route('/'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
