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

class Dh12Resource extends Resource
{
    protected static ?string $model = Dh12::class;
    protected static ?string $navigationLabel = 'Conceptos';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $activeNavigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Conceptos';

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
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('vig_coano')->label('Vig. año')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vig_comes')->label('Vig. mes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('desc_conce')->label('Descripción')
                    ->searchable(),
                Tables\Columns\TextColumn::make('desc_corta')->toggleable()->toggledHiddenByDefault()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_conce')->toggleable()->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('codc_vige1')->toggleable()->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('desc_nove1')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_nove1')->toggleable()->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('cant_ente1')->toggleable()->toggledHiddenByDefault()
                    ->numeric(),
                Tables\Columns\TextColumn::make('cant_deci1')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('codc_vige2')->toggleable()->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('desc_nove2')->toggleable(),
                Tables\Columns\TextColumn::make('tipo_nove2')->toggleable()->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('cant_ente2')->toggleable()->toggledHiddenByDefault()
                    ->numeric(),
                Tables\Columns\TextColumn::make('cant_deci2')->toggleable()->toggledHiddenByDefault()
                    ->numeric(),
                Tables\Columns\TextColumn::make('flag_acumu')->toggleable()->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('flag_grupo')->toggleable()->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('nro_orcal')->toggleable()->toggledHiddenByDefault()
                    ->numeric(),
                Tables\Columns\TextColumn::make('nro_orimp')->toggleable()->toggledHiddenByDefault()
                    ->numeric(),
                Tables\Columns\TextColumn::make('sino_legaj'),
                Tables\Columns\TextColumn::make('tipo_distr')->toggleable()->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('tipo_ganan')
                    ->numeric(),
                Tables\Columns\IconColumn::make('chk_acumsac')->label('SAC')
                    ->boolean(),
                Tables\Columns\IconColumn::make('chk_acumproy')->toggleable()->toggledHiddenByDefault()
                    ->boolean(),
                Tables\Columns\IconColumn::make('chk_dcto3')->toggleable()->toggledHiddenByDefault()
                    ->boolean(),
                Tables\Columns\IconColumn::make('chkacumprhbrprom')
                    ->boolean(),
                Tables\Columns\TextColumn::make('subcicloliquida')->toggleable()->toggledHiddenByDefault()
                    ->numeric(),
                Tables\Columns\IconColumn::make('chkdifhbrcargoasoc')
                    ->boolean(),
                Tables\Columns\IconColumn::make('chkptesubconcep')
                    ->boolean(),
                Tables\Columns\IconColumn::make('chkinfcuotasnovper')->toggleable()->toggledHiddenByDefault()
                    ->boolean(),
                Tables\Columns\IconColumn::make('genconimp0')->toggleable()->toggledHiddenByDefault()
                    ->boolean(),
                Tables\Columns\TextColumn::make('sino_visible')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                // aqui filtrar por tipo_conce
                Tables\Filters\SelectFilter::make('tipo_conce')
                    ->options([
                        'C' => 'Tipo C',
                        'S' => 'Tipo S',
                        'O' => 'Tipo O',
                        'F' => 'Tipo F',
                        'A' => 'Tipo A',
                    ])

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('codn_conce', 'asc');
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
