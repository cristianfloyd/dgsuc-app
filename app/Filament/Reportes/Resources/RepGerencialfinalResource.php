<?php

namespace App\Filament\Reportes\Resources;

use App\Filament\Reportes\Resources\RepGerencialfinalResource\Pages;
use App\Models\Mapuche\Catalogo\Dh30;
use App\Models\Mapuche\Catalogo\Dh36;
use App\Models\Reportes\RepGerencialFinal;
use App\Traits\MapucheConnectionTrait;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RepGerencialFinalResource extends Resource
{
    use MapucheConnectionTrait;

    protected static ?string $model = RepGerencialFinal::class;

    protected static ?string $label = 'Reporte Gerencial';

    protected static ?string $pluralLabel = 'Reporte Gerencial';

    protected static ?string $navigationGroup = 'Informes';

    protected static ?string $navigationLabel = 'Reporte Gerencial';

    protected static ?int $navigationSort = 1;

    private static $connectionInstance;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_legaj')
                    ->label('Legajo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('desc_apyno')
                    ->label('Apellido y Nombre')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('codn_depen'),
                TextColumn::make('coddependesemp')
                    ->label('Dependencia')
                    ->sortable(),
                TextColumn::make('codc_uacad'),
                TextColumn::make('imp_bruto')
                    ->label('Importe Bruto')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('imp_neto')
                    ->label('Importe Neto')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('imp_dctos')
                    ->label('Descuentos')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('nro_liqui')
                    ->label('Nro Liquidación')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('coddependesemp')
                    ->label('Dependencia')
                    ->relationship('dependencia', 'descdependesemp')
                    ->searchable()
                    ->preload(10)
                    ->optionsLimit(50)
                    ->getSearchResultsUsing(
                        fn (string $search): array => Dh36::query()
                            ->select('coddependesemp', 'descdependesemp')
                            ->where('descdependesemp', 'like', "%{$search}%")
                            ->orderBy('descdependesemp')
                            ->limit(50)
                            ->pluck('descdependesemp', 'coddependesemp')
                            ->toArray(),
                    )
                    ->getOptionLabelUsing(fn ($value): ?string => Dh36::find($value)?->descdependesemp),

                SelectFilter::make('codc_uacad')
                    ->label('Unidad Académica')
                    ->options(
                        fn () => Dh30::query()
                            ->where('nro_tabla', 13)
                            ->select('desc_abrev', 'desc_item')
                            ->orderBy('desc_item')
                            ->pluck('desc_item', 'desc_abrev')
                            ->toArray(),
                    )
                    ->searchable()
                    ->preload(),

                // SelectFilter::make('codc_uacad')
                //     ->label('Unidad Académica')
                //     ->options(fn() => DB::connection(self::getMapucheConnection()->getName())
                //         ->table('mapuche.dh30')
                //         ->where('nro_tabla', 13)
                //         ->orderBy('desc_item')
                //         ->pluck('desc_item', 'desc_abrev')
                //         ->toArray())
                //     ->searchable()
                //     ->preload(),

                SelectFilter::make('tipo_escal')
                    ->label('Escalafón')
                    ->options([
                        'S' => 'Simple',
                        'D' => 'Docente',
                        'N' => 'Nodocente',
                    ]),


                Filter::make('nro_legaj')
                    ->form([
                        TextInput::make('legajo')
                            ->numeric()
                            ->label('Número de Legajo'),
                    ])
                    ->query(
                        fn (Builder $query, array $data): Builder =>
                        $query->when(
                            isset($data['legajo']) && $data['legajo'],
                            fn (Builder $query, $value): Builder => $query->where('nro_legaj', $data['legajo']),
                        ),
                    ),

                Filter::make('en_banco')
                    ->label('Cobra por Banco')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query
                        ->where('en_banco', 'S')),

                SelectFilter::make('nro_liqui')
                    ->label('Liquidación')
                    ->multiple()
                    ->preload()
                    ->options(fn () => DB::connection(self::getMapucheConnection()->getName())
                        ->table('mapuche.dh22')
                        ->orderBy('nro_liqui', 'desc')
                        ->pluck('desc_liqui', 'nro_liqui')
                        ->toArray()),
            ])
            ->filtersFormColumns(3)
            ->actions([

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRepGerencialfinal::route('/'),
        ];
    }

    protected static function getMapucheConnection()
    {
        if (self::$connectionInstance === null) {
            $model = new static();
            self::$connectionInstance = $model->getConnectionFromTrait();
        }
        return self::$connectionInstance;
    }
}
