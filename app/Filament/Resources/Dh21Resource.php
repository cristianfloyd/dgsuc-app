<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Dh21;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Dh21Resource\Pages;
use App\Filament\Resources\Dh21Resource\Widgets\Dh21LegajoCounter;
use App\Filament\Resources\Dh21Resource\Widgets\Dh21Concepto101Total;

class Dh21Resource extends Resource
{
    protected static ?string $model = Dh21::class;
    protected static ?string $modelLabel = 'Liquidaciones';
    protected static ?string $navigationLabel = 'Liquidaciones';


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Liquidaciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nro_liqui')
                    ->numeric(),
                Forms\Components\TextInput::make('nro_legaj')
                    ->numeric(),
                Forms\Components\TextInput::make('nro_cargo')
                    ->numeric(),
                Forms\Components\TextInput::make('codn_conce')
                    ->numeric(),
                Forms\Components\TextInput::make('impp_conce')
                    ->numeric(),
                Forms\Components\TextInput::make('tipo_conce')
                    ->maxLength(1),
                Forms\Components\TextInput::make('nov1_conce')
                    ->numeric(),
                Forms\Components\TextInput::make('nov2_conce')
                    ->numeric(),
                Forms\Components\TextInput::make('nro_orimp')
                    ->numeric(),
                Forms\Components\TextInput::make('tipoescalafon')
                    ->maxLength(1),
                Forms\Components\TextInput::make('nrogrupoesc')
                    ->numeric(),
                Forms\Components\TextInput::make('codigoescalafon')
                    ->maxLength(4),
                Forms\Components\TextInput::make('codc_regio')
                    ->maxLength(4),
                Forms\Components\TextInput::make('codc_uacad')
                    ->maxLength(4),
                Forms\Components\TextInput::make('codn_area')
                    ->numeric(),
                Forms\Components\TextInput::make('codn_subar')
                    ->numeric(),
                Forms\Components\TextInput::make('codn_fuent')
                    ->numeric(),
                Forms\Components\TextInput::make('codn_progr')
                    ->numeric(),
                Forms\Components\TextInput::make('codn_subpr')
                    ->numeric(),
                Forms\Components\TextInput::make('codn_proye')
                    ->numeric(),
                Forms\Components\TextInput::make('codn_activ')
                    ->numeric(),
                Forms\Components\TextInput::make('codn_obra')
                    ->numeric(),
                Forms\Components\TextInput::make('codn_final')
                    ->numeric(),
                Forms\Components\TextInput::make('codn_funci')
                    ->numeric(),
                Forms\Components\TextInput::make('ano_retro')
                    ->numeric(),
                Forms\Components\TextInput::make('mes_retro')
                    ->numeric(),
                Forms\Components\TextInput::make('detallenovedad')
                    ->maxLength(10),
                Forms\Components\TextInput::make('codn_grupo_presup')
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('tipo_ejercicio')
                    ->maxLength(1)
                    ->default('A'),
                Forms\Components\TextInput::make('codn_subsubar')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_liqui')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nro_legaj')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nro_cargo')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_conce')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('impp_conce')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tipo_conce')
                    ->searchable(),
                TextColumn::make('nov1_conce')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nov2_conce')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nro_orimp')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tipoescalafon'),
                TextColumn::make('nrogrupoesc')
                    ->numeric(),
                TextColumn::make('codigoescalafon'),
                TextColumn::make('codc_regio'),
                TextColumn::make('codc_uacad'),
                TextColumn::make('codn_area')
                    ->numeric(),
                TextColumn::make('codn_subar')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_fuent')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_progr')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_subpr')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_proye')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_activ')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_obra')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_final')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codn_funci')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ano_retro')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('mes_retro')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('detallenovedad')
                    ->searchable(),
                TextColumn::make('codn_grupo_presup')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tipo_ejercicio')
                    ->searchable(),
                TextColumn::make('codn_subsubar')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nro_legaj', 'desc')
            ->paginated(5) //configurar la paginacion
            ->paginationPageOptions([5, 10, 25, 50, 100, 250, 500, 1000])
        ;
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
            'index' => Pages\ListDh21s::route('/'),
            'create' => Pages\CreateDh21::route('/create'),
            'edit' => Pages\EditDh21::route('/{record}/edit'),
            'conceptos-totales' => Pages\ConceptosTotales::route('/conceptos-totales'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Dh21LegajoCounter::class,
            Dh21Concepto101Total::class,
        ];
    }
}
