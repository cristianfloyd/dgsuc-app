<?php

declare(strict_types=1);

namespace App\Filament\Afip\Resources\AfipMapucheSicossCalculos\AfipMapucheSicossCalculos;

use App\Filament\Afip\Resources\AfipMapucheSicossCalculos\Pages\EditAfipMapucheSicossCalculo;
use App\Filament\Afip\Resources\AfipMapucheSicossCalculos\Pages\ImportAfipMapucheSicossCalculo;
use App\Filament\Afip\Resources\AfipMapucheSicossCalculos\Pages\ListAfipMapucheSicossCalculos;
use App\Models\AfipMapucheSicossCalculo;
use App\Repositories\Contracts\AfipMapucheSicossCalculoRepository;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AfipMapucheSicossCalculoResource extends Resource
{
    protected static ?string $model = AfipMapucheSicossCalculo::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-left-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'AFIP';

    protected static ?string $navigationLabel = 'SICOSS Calculo';

    protected static ?int $navigationSort = 2;

    public static function getPages(): array
    {
        return [
            'index' => ListAfipMapucheSicossCalculos::route('/'),
            'edit' => EditAfipMapucheSicossCalculo::route('/{record}/edit'),
            'import' => ImportAfipMapucheSicossCalculo::route('/import'),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periodo_fiscal')
                    ->label('Período Fiscal')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cuil')
                    ->copyable()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('remtotal')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('codc_uacad')
                    ->label('UA/CAD')
                    ->searchable(),
                TextColumn::make('caracter')
                    ->searchable(),
                TextColumn::make('rem1')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('rem2')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('aportesijp')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('aporteinssjp')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('contribucionsijp')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('contribucioninssjp')
                    ->money('ARS')
                    ->sortable(),
                TextColumn::make('aportediferencialsijp')
                    ->money('ARS')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('codc_uacad')
                    ->label('UA/CAD'),
                SelectFilter::make('caracter'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                \Filament\Actions\ActionGroup::make([
                    Action::make('import')
                        ->label('Importar SICOSS Calculos')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('primary')
                        ->url(fn (): string => static::getUrl('import'))
                        ->button(),

                    Action::make('truncateTable')
                        ->label('Vaciar Tabla')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('¿Vaciar tabla?')
                        ->modalDescription('Esta acción eliminará todos los registros de la tabla. Esta operación no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, vaciar tabla')
                        ->action(function (): void {
                            app(AfipMapucheSicossCalculoRepository::class)->truncate();
                            Notification::make()
                                ->success()
                                ->title('Tabla vaciada')
                                ->body('Se han eliminado todos los registros correctamente')
                                ->send();
                        }),
                ])
                    ->icon('heroicon-o-cog-8-tooth')
                    ->tooltip('Acciones')
                    ->size('lg'),
            ])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No se encontraron registros')
            ->emptyStateDescription('No se encontraron registros en la tabla. Puedes importar nuevos registros o vaciar la tabla.')
            ->emptyStateActions([
                Action::make('import')
                    ->label('Importar SICOSS Calculos')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->url(fn (): string => static::getUrl('import'))
                    ->button(),
                Action::make('truncateTable')
                    ->label('Vaciar Tabla')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Vaciar tabla?')
                    ->modalDescription('Esta acción eliminará todos los registros de la tabla. Esta operación no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, vaciar tabla')
                    ->action(function (): void {
                        app(AfipMapucheSicossCalculoRepository::class)->truncate();
                        Notification::make()
                            ->success()
                            ->title('Tabla vaciada')
                            ->body('Se han eliminado todos los registros correctamente')
                            ->send();
                    }),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('cuil')
                    ->required()
                    ->maxLength(11),
                TextInput::make('remtotal')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('rem1')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('rem2')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('codc_uacad')
                    ->required()
                    ->maxLength(3),
                TextInput::make('caracter')
                    ->required()
                    ->maxLength(4),
            ]);
    }
}
