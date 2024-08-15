<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Dh21Resource\Pages;
use App\Models\Dh21;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                Tables\Columns\TextColumn::make('nro_liqui')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nro_legaj')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nro_cargo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_conce')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('impp_conce')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_conce')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nov1_conce')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nov2_conce')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nro_orimp')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipoescalafon'),
                Tables\Columns\TextColumn::make('nrogrupoesc')
                    ->numeric(),
                Tables\Columns\TextColumn::make('codigoescalafon'),
                Tables\Columns\TextColumn::make('codc_regio'),
                Tables\Columns\TextColumn::make('codc_uacad'),
                Tables\Columns\TextColumn::make('codn_area')
                    ->numeric(),
                Tables\Columns\TextColumn::make('codn_subar')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_fuent')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_progr')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_subpr')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_proye')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_activ')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_obra')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_final')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codn_funci')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ano_retro')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mes_retro')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('detallenovedad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codn_grupo_presup')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_ejercicio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codn_subsubar')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
                SelectFilter::make('codn_conce')
                    ->multiple()
                    ->options([
                        '101' => '1 - Sueldo',
                        '102' => '2 - Viaticos',
                        '103' => '3 - Antiguedad',
                    ])
                    ->label('Tipo de Concepto'),
                SelectFilter::make('nro_liqui')
                    ->options([
                        '1' => '1 - Enero',
                        '2' => '2 - Febrero',
                        '3' => '3 - Marzo',
                        '4' => '4 - def marzo',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nro_legaj', 'desc')
            ->paginated(5) //configurar la paginacion
            ->paginationPageOptions([5,10,25,50,100, 250, 500, 1000])
            ->searchable('nro_legaj','nro_cargo');
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
        ];
    }
}
