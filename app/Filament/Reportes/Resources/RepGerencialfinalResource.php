<?php

namespace App\Filament\Reportes\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Filters\Filter;
use App\Models\Mapuche\Catalogo\Dh30;
use App\Models\Mapuche\Catalogo\Dh36;
use App\Models\Mapuche\Catalogo\Dhe4;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Reportes\RepGerencialFinal;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Reportes\Resources\RepGerencialfinalResource\Pages;
use App\Filament\Reportes\Resources\RepGerencialfinalResource\RelationManagers;

class RepGerencialFinalResource extends Resource
{
    use MapucheConnectionTrait;
    private static $connectionInstance = null;
    protected static ?string $model = RepGerencialFinal::class;
    protected static ?string $label = 'Reporte Gerencial';
    protected static ?string $pluralLabel = 'Reporte Gerencial';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Reportes';
    protected static ?string $navigationLabel = 'Reporte Gerencial';
    protected static ?int $navigationSort = 1;


    protected static function getMapucheConnection()
    {
        if (self::$connectionInstance === null) {
            $model = new static;
            self::$connectionInstance = $model->getConnectionFromTrait();
        }
        return self::$connectionInstance;
    }




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
                                    ->toArray()
                    )
                    ->getOptionLabelUsing(fn ($value): ?string => Dh36::find($value)?->descdependesemp),

                SelectFilter::make('codc_uacad')
                    ->label('Unidad Académica')
                    ->options(fn() => Dh30::query()
                        ->where('nro_tabla', 13)
                        ->select('desc_abrev', 'desc_item')
                        ->orderBy('desc_item')
                        ->pluck('desc_item', 'desc_abrev')
                        ->toArray()
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
                        'N' => 'Nodocente'
                    ]),

                // Filter::make('imp_bruto')
                //     ->form([
                //         TextInput::make('desde')
                //             ->numeric()
                //             ->label('Importe desde'),
                //         TextInput::make('hasta')
                //             ->numeric()
                //             ->label('Importe hasta'),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 isset($data['desde']) && $data['desde'],
                //                 fn (Builder $query, $value): Builder => $query->where('imp_bruto', '>=', $data['desde']),
                //             )
                //             ->when(
                //                 isset($data['hasta']) && $data['hasta'],
                //                 fn (Builder $query, $value): Builder => $query->where('imp_bruto', '<=', $data['hasta']),
                //             );
                //     }),

                Filter::make('nro_legaj')
                    ->form([
                        TextInput::make('legajo')
                            ->numeric()
                            ->label('Número de Legajo')
                    ])
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query->when(
                            isset($data['legajo']) && $data['legajo'],
                            fn (Builder $query, $value): Builder => $query->where('nro_legaj', $data['legajo'])
                        )
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
                    ->options(fn() => DB::connection(self::getMapucheConnection()->getName())
                        ->table('mapuche.dh22')
                        ->orderBy('nro_liqui', 'desc')
                        ->pluck('desc_liqui', 'nro_liqui')
                        ->toArray()),
            ])
            ->filtersFormColumns(3)
            ->actions([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRepGerencialfinal::route('/'),
        ];
    }
}
