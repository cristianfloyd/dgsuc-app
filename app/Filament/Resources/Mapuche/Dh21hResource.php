<?php

namespace App\Filament\Resources\Mapuche;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Mapuche\Dh21h;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Mapuche\Dh21hResource\Pages;
use App\Filament\Resources\Mapuche\Dh21hResource\RelationManagers;

class Dh21hResource extends Resource
{
    protected static ?string $model = Dh21h::class;

    protected static ?string $modelLabel = 'Historial de Liquidaciones';
    protected static ?string $navigationLabel = 'Historial de Liquidaciones';


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Liquidaciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('nro_liqui')
                //     ->numeric(),
                // Forms\Components\TextInput::make('nro_legaj')
                //     ->numeric(),
                // Forms\Components\TextInput::make('nro_cargo')
                //     ->numeric(),
                // Forms\Components\TextInput::make('codn_conce')
                //     ->numeric(),
                // Forms\Components\TextInput::make('impp_conce')
                //     ->numeric(),
                // Forms\Components\TextInput::make('tipo_conce')
                //     ->maxLength(1),
                // Forms\Components\TextInput::make('nov1_conce')
                //     ->numeric(),
                // Forms\Components\TextInput::make('nov2_conce')
                //     ->numeric(),
                // Forms\Components\TextInput::make('nro_orimp')
                //     ->numeric(),
                // Forms\Components\TextInput::make('tipoescalafon')
                //     ->maxLength(1),
                // Forms\Components\TextInput::make('nrogrupoesc')
                //     ->numeric(),
                // Forms\Components\TextInput::make('codigoescalafon')
                //     ->maxLength(4),
                // Forms\Components\TextInput::make('codc_regio')
                //     ->maxLength(4),
                // Forms\Components\TextInput::make('codc_uacad')
                //     ->maxLength(4),
                // Forms\Components\TextInput::make('codn_area')
                //     ->numeric(),
                // Forms\Components\TextInput::make('codn_subar')
                //     ->numeric(),
                // Forms\Components\TextInput::make('codn_fuent')
                //     ->numeric(),
                // Forms\Components\TextInput::make('codn_progr')
                //     ->numeric(),
                // Forms\Components\TextInput::make('codn_subpr')
                //     ->numeric(),
                // Forms\Components\TextInput::make('codn_proye')
                //     ->numeric(),
                // Forms\Components\TextInput::make('codn_activ')
                //     ->numeric(),
                // Forms\Components\TextInput::make('codn_obra')
                //     ->numeric(),
                // Forms\Components\TextInput::make('codn_final')
                //     ->numeric(),
                // Forms\Components\TextInput::make('codn_funci')
                //     ->numeric(),
                // Forms\Components\TextInput::make('ano_retro')
                //     ->numeric(),
                // Forms\Components\TextInput::make('mes_retro')
                //     ->numeric(),
                // Forms\Components\TextInput::make('detallenovedad')
                //     ->maxLength(10),
                // Forms\Components\TextInput::make('codn_grupo_presup')
                //     ->numeric()
                //     ->default(1),
                // Forms\Components\TextInput::make('tipo_ejercicio')
                //     ->maxLength(1)
                //     ->default('A'),
                // Forms\Components\TextInput::make('codn_subsubar')
                //     ->numeric()
                //     ->default(0),
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
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('tipoescalafon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nrogrupoesc')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codigoescalafon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codc_regio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codc_uacad')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codn_area')
                    ->numeric()
                    ->sortable(),
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
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDh21hs::route('/'),
            // 'create' => Pages\CreateDh21h::route('/create'),
            // 'edit' => Pages\EditDh21h::route('/{record}/edit'),
        ];
    }
}
