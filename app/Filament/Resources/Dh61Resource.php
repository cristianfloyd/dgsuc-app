<?php

namespace App\Filament\Resources;

use App\Filament\Resources\Dh61Resource\Pages;
use App\Models\Dh61;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class Dh61Resource extends Resource
{
    protected static ?string $model = Dh61::class;
    protected static ?string $modelLabel = 'Categorias Historico';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Personal';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codc_categ')
                    ->required()
                    ->maxLength(4),
                Forms\Components\TextInput::make('equivalencia')
                    ->maxLength(3),
                Forms\Components\TextInput::make('tipo_escal')
                    ->maxLength(1),
                Forms\Components\TextInput::make('nro_escal')
                    ->numeric(),
                Forms\Components\TextInput::make('impp_basic')
                    ->numeric(),
                Forms\Components\TextInput::make('codc_dedic')
                    ->maxLength(4),
                Forms\Components\TextInput::make('sino_mensu')
                    ->maxLength(1),
                Forms\Components\TextInput::make('sino_djpat')
                    ->maxLength(1),
                Forms\Components\TextInput::make('vig_caano')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('vig_cames')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('desc_categ')
                    ->maxLength(20),
                Forms\Components\TextInput::make('sino_jefat')
                    ->maxLength(1),
                Forms\Components\TextInput::make('impp_asign')
                    ->numeric(),
                Forms\Components\TextInput::make('computaantig')
                    ->numeric(),
                Forms\Components\Toggle::make('controlcargos'),
                Forms\Components\TextInput::make('controlhoras')
                    ->numeric(),
                Forms\Components\TextInput::make('controlpuntos')
                    ->numeric(),
                Forms\Components\TextInput::make('controlpresup')
                    ->numeric(),
                Forms\Components\TextInput::make('horasmenanual')
                    ->maxLength(1),
                Forms\Components\TextInput::make('cantpuntos')
                    ->numeric(),
                Forms\Components\TextInput::make('estadolaboral')
                    ->maxLength(1),
                Forms\Components\TextInput::make('nivel')
                    ->maxLength(3),
                Forms\Components\TextInput::make('tipocargo')
                    ->maxLength(30),
                Forms\Components\TextInput::make('remunbonif')
                    ->numeric(),
                Forms\Components\TextInput::make('noremunbonif')
                    ->numeric(),
                Forms\Components\TextInput::make('remunnobonif')
                    ->numeric(),
                Forms\Components\TextInput::make('noremunnobonif')
                    ->numeric(),
                Forms\Components\TextInput::make('otrasrem')
                    ->numeric(),
                Forms\Components\TextInput::make('dto1610')
                    ->numeric(),
                Forms\Components\TextInput::make('reflaboral')
                    ->numeric(),
                Forms\Components\TextInput::make('refadm95')
                    ->numeric(),
                Forms\Components\TextInput::make('critico')
                    ->numeric(),
                Forms\Components\TextInput::make('jefatura')
                    ->numeric(),
                Forms\Components\TextInput::make('gastosrepre')
                    ->numeric(),
                Forms\Components\TextInput::make('codigoescalafon')
                    ->maxLength(4),
                Forms\Components\TextInput::make('noinformasipuver')
                    ->numeric(),
                Forms\Components\TextInput::make('noinformasirhu')
                    ->numeric(),
                Forms\Components\TextInput::make('imppnooblig')
                    ->numeric(),
                Forms\Components\TextInput::make('aportalao')
                    ->numeric(),
                Forms\Components\TextInput::make('factor_hs_catedra')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_compuesto'),
                TextColumn::make('codc_categ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('equivalencia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_escal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nro_escal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('impp_basic')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codc_dedic')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sino_mensu')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sino_djpat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vig_caano')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vig_cames')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('desc_categ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sino_jefat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('impp_asign')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('computaantig')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('controlcargos')
                    ->boolean(),
                Tables\Columns\TextColumn::make('controlhoras')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('controlpuntos')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('controlpresup')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('horasmenanual')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cantpuntos')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estadolaboral')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nivel')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipocargo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('remunbonif')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('noremunbonif')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('remunnobonif')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('noremunnobonif')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('otrasrem')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dto1610')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reflaboral')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('refadm95')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('critico')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jefatura')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gastosrepre')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codigoescalafon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('noinformasipuver')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('noinformasirhu')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('imppnooblig')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('aportalao')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('factor_hs_catedra')
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
            'index' => Pages\ListDh61s::route('/'),
            'create' => Pages\CreateDh61::route('/create'),
            'edit' => Pages\EditDh61::route('/{record}/edit'),
        ];
    }
}
