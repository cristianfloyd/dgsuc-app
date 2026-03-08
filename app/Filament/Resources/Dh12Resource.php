<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use App\Filament\Resources\Dh12Resource\Pages\ListDh12s;
use App\Filament\Resources\Dh12Resource\Pages\CreateDh12;
use App\Filament\Resources\Dh12Resource\Pages\EditDh12;
use App\Filament\Resources\Dh12Resource\Pages;
use App\Models\Dh12;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class Dh12Resource extends Resource
{
    protected static ?string $model = Dh12::class;

    protected static ?string $navigationLabel = 'Conceptos';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string | \BackedEnum | null $activeNavigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Conceptos';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('vig_coano')
                    ->numeric(),
                TextInput::make('vig_comes')
                    ->numeric(),
                TextInput::make('desc_conce')
                    ->maxLength(30),
                TextInput::make('desc_corta')
                    ->maxLength(15),
                TextInput::make('tipo_conce')
                    ->required()
                    ->maxLength(1)
                    ->default('C'),
                TextInput::make('codc_vige1')
                    ->maxLength(1),
                TextInput::make('desc_nove1')
                    ->maxLength(15),
                TextInput::make('tipo_nove1')
                    ->maxLength(1),
                TextInput::make('cant_ente1')
                    ->numeric(),
                TextInput::make('cant_deci1')
                    ->numeric(),
                TextInput::make('codc_vige2')
                    ->maxLength(1),
                TextInput::make('desc_nove2')
                    ->maxLength(15),
                TextInput::make('tipo_nove2')
                    ->maxLength(1),
                TextInput::make('cant_ente2')
                    ->numeric(),
                TextInput::make('cant_deci2')
                    ->numeric(),
                TextInput::make('flag_acumu')
                    ->maxLength(99),
                TextInput::make('flag_grupo')
                    ->maxLength(90),
                TextInput::make('nro_orcal')
                    ->required()
                    ->numeric(),
                TextInput::make('nro_orimp')
                    ->required()
                    ->numeric(),
                TextInput::make('sino_legaj')
                    ->required()
                    ->maxLength(1)
                    ->default('N'),
                TextInput::make('tipo_distr')
                    ->maxLength(1),
                TextInput::make('tipo_ganan')
                    ->numeric(),
                Toggle::make('chk_acumsac'),
                Toggle::make('chk_acumproy'),
                Toggle::make('chk_dcto3'),
                Toggle::make('chkacumprhbrprom'),
                TextInput::make('subcicloliquida')
                    ->numeric(),
                Toggle::make('chkdifhbrcargoasoc'),
                Toggle::make('chkptesubconcep'),
                Toggle::make('chkinfcuotasnovper'),
                Toggle::make('genconimp0'),
                TextInput::make('sino_visible')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codn_conce')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('vig_coano')->label('Vig. año')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('vig_comes')->label('Vig. mes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('desc_conce')->label('Descripción')
                    ->searchable(),
                TextColumn::make('desc_corta')->toggleable()->toggledHiddenByDefault()
                    ->searchable(),
                TextColumn::make('tipo_conce')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('codc_vige1')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('desc_nove1')
                    ->searchable(),
                TextColumn::make('tipo_nove1')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('cant_ente1')->toggleable()->toggledHiddenByDefault()
                    ->numeric(),
                TextColumn::make('cant_deci1')->toggleable()->toggledHiddenByDefault()
                    ->numeric()
                    ->sortable(),
                TextColumn::make('codc_vige2')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('desc_nove2')->toggleable(),
                TextColumn::make('tipo_nove2')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('cant_ente2')->toggleable()->toggledHiddenByDefault()
                    ->numeric(),
                TextColumn::make('cant_deci2')->toggleable()->toggledHiddenByDefault()
                    ->numeric(),
                TextColumn::make('flag_acumu')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('flag_grupo')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('nro_orcal')->toggleable()->toggledHiddenByDefault()
                    ->numeric(),
                TextColumn::make('nro_orimp')->toggleable()->toggledHiddenByDefault()
                    ->numeric(),
                TextColumn::make('sino_legaj'),
                TextColumn::make('tipo_distr')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('tipo_ganan')
                    ->numeric(),
                IconColumn::make('chk_acumsac')->label('SAC')
                    ->boolean(),
                IconColumn::make('chk_acumproy')->toggleable()->toggledHiddenByDefault()
                    ->boolean(),
                IconColumn::make('chk_dcto3')->toggleable()->toggledHiddenByDefault()
                    ->boolean(),
                IconColumn::make('chkacumprhbrprom')
                    ->boolean(),
                TextColumn::make('subcicloliquida')->toggleable()->toggledHiddenByDefault()
                    ->numeric(),
                IconColumn::make('chkdifhbrcargoasoc')
                    ->boolean(),
                IconColumn::make('chkptesubconcep')
                    ->boolean(),
                IconColumn::make('chkinfcuotasnovper')->toggleable()->toggledHiddenByDefault()
                    ->boolean(),
                IconColumn::make('genconimp0')->toggleable()->toggledHiddenByDefault()
                    ->boolean(),
                TextColumn::make('sino_visible')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                // aqui filtrar por tipo_conce
                SelectFilter::make('tipo_conce')
                    ->options([
                        'C' => 'Tipo C',
                        'S' => 'Tipo S',
                        'O' => 'Tipo O',
                        'F' => 'Tipo F',
                        'A' => 'Tipo A',
                    ]),

            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('codn_conce', 'asc');
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDh12s::route('/'),
            'create' => CreateDh12::route('/create'),
            'edit' => EditDh12::route('/{record}/edit'),
        ];
    }
}
