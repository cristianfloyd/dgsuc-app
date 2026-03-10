<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonalResource\Pages\CreatePersonal;
use App\Filament\Resources\PersonalResource\Pages\EditPersonal;
use App\Filament\Resources\PersonalResource\Pages\ListPersonals;
use App\Models\Dh01;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PersonalResource extends Resource
{
    protected static ?string $model = Dh01::class;

    protected static ?string $label = 'Personal';

    protected static ?string $plurallabel = 'Personal';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string | UnitEnum | null $navigationGroup = 'Personal';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_legaj')->searchable(),
                TextColumn::make('desc_appat')->toggleable(true)->searchable(),
                TextColumn::make('desc_apmat')->toggleable(true)->searchable(),
                TextColumn::make('desc_apcas')->toggleable(true)->searchable(),
                TextColumn::make('desc_nombr')->toggleable(true)->searchable(),
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


            ])
            ->recordActions([
                // Tables\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('nro_legaj', 'desc')
            ->paginated(5) //configurar la paginacion
            ->paginationPageOptions([5, 10, 25, 50, 100, 250])
            ->searchable();
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPersonals::route('/'),
            'create' => CreatePersonal::route('/create'),
            'edit' => EditPersonal::route('/{record}/edit'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where(function (Builder $query, $searchTerm): void {
            $query->orWhereRaw(
                "CONCAT(tipo_docum, '-', nro_docum) ILIKE ?",
                ["%$searchTerm%"],
            );
        });
    }
}
