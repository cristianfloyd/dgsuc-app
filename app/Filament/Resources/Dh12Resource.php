<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Dh12Resource\Pages;
use App\Filament\Resources\Dh12Resource\RelationManagers;
use App\Models\Dh12;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class Dh12Resource extends Resource
{
    protected static ?string $model = Dh12::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('vig_coano')
                    ->numeric(),
                Forms\Components\TextInput::make('vig_comes')
                    ->numeric(),
                Forms\Components\TextInput::make('desc_conce')
                    ->maxLength(30),
                Forms\Components\TextInput::make('desc_corta')
                    ->maxLength(15),
                Forms\Components\TextInput::make('tipo_conce')
                    ->required()
                    ->maxLength(1)
                    ->default('C'),
                Forms\Components\TextInput::make('codc_vige1')
                    ->maxLength(1),
                Forms\Components\TextInput::make('desc_nove1')
                    ->maxLength(15),
                Forms\Components\TextInput::make('tipo_nove1')
                    ->maxLength(1),
                Forms\Components\TextInput::make('cant_ente1')
                    ->numeric(),
                Forms\Components\TextInput::make('cant_deci1')
                    ->numeric(),
                Forms\Components\TextInput::make('codc_vige2')
                    ->maxLength(1),
                Forms\Components\TextInput::make('desc_nove2')
                    ->maxLength(15),
                Forms\Components\TextInput::make('tipo_nove2')
                    ->maxLength(1),
                Forms\Components\TextInput::make('cant_ente2')
                    ->numeric(),
                Forms\Components\TextInput::make('cant_deci2')
                    ->numeric(),
                Forms\Components\TextInput::make('flag_acumu')
                    ->maxLength(99),
                Forms\Components\TextInput::make('flag_grupo')
                    ->maxLength(90),
                Forms\Components\TextInput::make('nro_orcal')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('nro_orimp')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('sino_legaj')
                    ->required()
                    ->maxLength(1)
                    ->default('N'),
                Forms\Components\TextInput::make('tipo_distr')
                    ->maxLength(1),
                Forms\Components\TextInput::make('tipo_ganan')
                    ->numeric(),
                Forms\Components\Toggle::make('chk_acumsac'),
                Forms\Components\Toggle::make('chk_acumproy'),
                Forms\Components\Toggle::make('chk_dcto3'),
                Forms\Components\Toggle::make('chkacumprhbrprom'),
                Forms\Components\TextInput::make('subcicloliquida')
                    ->numeric(),
                Forms\Components\Toggle::make('chkdifhbrcargoasoc'),
                Forms\Components\Toggle::make('chkptesubconcep'),
                Forms\Components\Toggle::make('chkinfcuotasnovper'),
                Forms\Components\Toggle::make('genconimp0'),
                Forms\Components\TextInput::make('sino_visible')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('codn_conce')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vig_coano')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vig_comes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('desc_conce')
                    ->searchable(),
                Tables\Columns\TextColumn::make('desc_corta')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_conce')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codc_vige1')
                    ->searchable(),
                Tables\Columns\TextColumn::make('desc_nove1')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_nove1')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cant_ente1')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cant_deci1')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codc_vige2')
                    ->searchable(),
                Tables\Columns\TextColumn::make('desc_nove2')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_nove2')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cant_ente2')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cant_deci2')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('flag_acumu')
                    ->searchable(),
                Tables\Columns\TextColumn::make('flag_grupo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nro_orcal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nro_orimp')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sino_legaj')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_distr')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_ganan')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('chk_acumsac')
                    ->boolean(),
                Tables\Columns\IconColumn::make('chk_acumproy')
                    ->boolean(),
                Tables\Columns\IconColumn::make('chk_dcto3')
                    ->boolean(),
                Tables\Columns\IconColumn::make('chkacumprhbrprom')
                    ->boolean(),
                Tables\Columns\TextColumn::make('subcicloliquida')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('chkdifhbrcargoasoc')
                    ->boolean(),
                Tables\Columns\IconColumn::make('chkptesubconcep')
                    ->boolean(),
                Tables\Columns\IconColumn::make('chkinfcuotasnovper')
                    ->boolean(),
                Tables\Columns\IconColumn::make('genconimp0')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sino_visible')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListDh12s::route('/'),
            'create' => Pages\CreateDh12::route('/create'),
            'edit' => Pages\EditDh12::route('/{record}/edit'),
        ];
    }
}
