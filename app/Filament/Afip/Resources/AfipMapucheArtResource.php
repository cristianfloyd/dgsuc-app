<?php

namespace App\Filament\Afip\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\AfipMapucheArt;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AfipMapucheArtExport;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Actions\PoblarAfipArtAction;
use App\Filament\Afip\Resources\AfipMapucheArtResource\Pages;

class AfipMapucheArtResource extends Resource
{
    protected static ?string $model = AfipMapucheArt::class;
    protected static ?string $modelLabel = 'Afip Art';
    protected static ?string $pluralModelLabel = 'Afip Art';
    protected static ?string $navigationGroup = 'AFIP';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextColumn::make('cuil_original')->label('CUIL'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cuil')->label('CUIL'),
                TextColumn::make('apellido_y_nombre')
                    ->label('Apellido y Nombre')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('apnom', 'ilike', '%' . strtoupper($search) . '%');
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                TextColumn::make('nacimiento')->label('Nacimiento')->date('d/m/Y'),
                TextColumn::make('sueldo')->label('Sueldo'),
                TextColumn::make('sexo')->label('Sexo'),
                TextColumn::make('dh30.desc_item')
                    ->label('Establecimiento')
                    ->searchable()
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
                                fn(Builder $query, $monto): Builder => $query->where('sueldo', '>', $monto),
                            );
                    }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Exportar a Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function ($livewire) {
                        return Excel::download(
                            new AfipMapucheArtExport(
                                session('periodo_fiscal', date('Ym')),
                                $livewire->getFilteredTableQuery()
                            ),
                            'reporte-afip-art-' . session('periodo_fiscal', date('Ym')) . '.xlsx'
                        );
                    })
            ])
            ->defaultPaginationPageOption(5);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
