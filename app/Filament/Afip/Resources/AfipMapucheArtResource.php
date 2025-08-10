<?php

namespace App\Filament\Afip\Resources;

use App\Filament\Actions\PoblarAfipArtAction;
use App\Filament\Afip\Resources\AfipMapucheArtResource\Pages;
use App\Models\AfipMapucheArt;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AfipMapucheArtResource extends Resource
{
    protected static ?string $model = AfipMapucheArt::class;

    protected static ?string $modelLabel = 'Afip Art';

    protected static ?string $pluralModelLabel = 'Afip Art';

    protected static ?string $navigationGroup = 'AFIP';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('cuil')->label('CUIL'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cuil')->label('CUIL')
                    ->copyable()
                    ->copyMessage('CUIL copiado al portapapeles') // Mensaje opcional de confirmación
                    ->copyMessageDuration(1500)
                    ->icon('heroicon-o-clipboard-document') // Icono opcional
                    ->tooltip('Haz clic para copiar'),
                TextColumn::make('apellido_y_nombre')
                    ->label('Apellido y Nombre')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($query) use ($search): void {
                            $query->where('apellido_y_nombre', 'ilike', '%' . strtoupper($search) . '%')
                                ->orWhere('cuil', 'ilike', '%' . $search . '%');
                        });
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                TextColumn::make('nro_legaj'),
                TextColumn::make('nacimiento')->label('Nacimiento')->date('d/m/Y'),
                TextColumn::make('sueldo')->label('Sueldo'),
                TextColumn::make('sexo')->label('Sexo'),
                TextColumn::make('dh30.desc_item')
                    ->label('Establecimiento')
                    ->sortable(),
                TextColumn::make('tarea')->label('Tarea'),
            ])
            ->filters([
                SelectFilter::make('sexo')
                    ->options([
                        'M' => 'Masculino',
                        'F' => 'Femenino',
                    ]),
                Filter::make('sueldo_mayor')
                    ->form([
                        TextInput::make('monto')
                            ->numeric()
                            ->label('Sueldo mayor a'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['monto'],
                                fn (Builder $query, $monto): Builder => $query->where('sueldo', '>', $monto),
                            );
                    }),
            ])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                ]),
            ])
            ->headerActions([

            ])
            ->defaultPaginationPageOption(5);
    }

    public static function getActions(): array
    {
        return [
            PoblarAfipArtAction::make()
                ->label('Poblar ART')
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray'),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAfipMapucheArt::route('/'),
            'create' => Pages\CreateAfipMapucheArt::route('/create'),
            'edit' => Pages\EditAfipMapucheArt::route('/{record}/edit'),
        ];
    }
}
