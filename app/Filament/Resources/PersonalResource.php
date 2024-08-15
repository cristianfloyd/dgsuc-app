<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Personal;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PersonalResource\Pages;

class PersonalResource extends Resource
{
    protected static ?string $model = Personal::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Personal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_legaj')
                    ->searchable(),
                    TextColumn::make('desc_appat')->toggleable(true)->toggledHiddenByDefault(),
                    TextColumn::make('desc_apmat')->toggleable(true),
                    TextColumn::make('desc_apcas')->toggleable(true),
                    TextColumn::make('desc_nombr')->toggleable(true),
                    TextColumn::make('nro_tabla')->toggleable(true),
                    TextColumn::make('tipo_docum')->toggleable(true),
                    TextColumn::make('nro_docum')->toggleable(true),
                    TextColumn::make('nro_cuil1')->toggleable(true),
                    TextColumn::make('nro_cuil')->toggleable(true),
                    TextColumn::make('nro_cuil2')->toggleable(true),
                    TextColumn::make('tipo_sexo')->toggleable(true),
                    TextColumn::make('fec_nacim')->toggleable(true),
                    TextColumn::make('tipo_facto')->toggleable(true),
                    TextColumn::make('tipo_rh')->toggleable(true),
                    TextColumn::make('nro_ficha')->toggleable(true),
                    TextColumn::make('tipo_estad')->toggleable(true),
                    TextColumn::make('nombrelugarnac')->toggleable(true),
                    TextColumn::make('periodoalta')->toggleable(true),
                    TextColumn::make('anioalta')->toggleable(true),
                    TextColumn::make('periodoactualizacion')->toggleable(true),
                    TextColumn::make('anioactualizacion')->toggleable(true),
                    TextColumn::make('pcia_nacim')->searchable()->toggleable(true),
                    TextColumn::make('pais_nacim')->searchable()->toggleable(true),
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
            ->paginationPageOptions([5,10,25,50,100, 250, 500, 1000])
            ->searchable();
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where(function (Builder $query, $searchTerm) {
            $query->orWhereRaw(
                "CONCAT(tipo_docum, '-', nro_docum) ILIKE ?",
                ["%$searchTerm%"]
            );
        });
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
            'index' => Pages\ListPersonals::route('/'),
            'create' => Pages\CreatePersonal::route('/create'),
            'edit' => Pages\EditPersonal::route('/{record}/edit'),
        ];
    }
}
