<?php

declare(strict_types=1);

namespace App\Filament\Afip\Resources;

use App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource\Pages\EditAfipMapucheSicossCalculo;
use App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource\Pages\ImportAfipMapucheSicossCalculo;
use App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource\Pages\ListAfipMapucheSicossCalculos;
use App\Models\AfipMapucheSicossCalculo;
use App\Repositories\Contracts\AfipMapucheSicossCalculoRepository;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action as ActionsTable;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;

class AfipMapucheSicossCalculoResource extends Resource
{
    protected static ?string $model = AfipMapucheSicossCalculo::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-left-circle';

    protected static ?string $navigationGroup = 'AFIP';

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

    public static function table(Tables\Table $table): Tables\Table
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
                Tables\Filters\SelectFilter::make('codc_uacad')
                    ->label('UA/CAD'),
                Tables\Filters\SelectFilter::make('caracter'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                ActionGroup::make([
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
                ActionsTable::make('import')
                    ->label('Importar SICOSS Calculos')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->url(fn (): string => static::getUrl('import'))
                    ->button(),
                ActionsTable::make('truncateTable')
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

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cuil')
                    ->required()
                    ->maxLength(11),
                Forms\Components\TextInput::make('remtotal')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('rem1')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('rem2')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('codc_uacad')
                    ->required()
                    ->maxLength(3),
                Forms\Components\TextInput::make('caracter')
                    ->required()
                    ->maxLength(4),
            ]);
    }
}
