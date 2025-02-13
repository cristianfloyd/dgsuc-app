<?php

declare(strict_types=1);

namespace App\Filament\Afip\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use App\Models\AfipMapucheSicossCalculo;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use App\Services\AfipMapucheSicossCalculoUpdateService;
use App\Repositories\Contracts\AfipMapucheSicossCalculoRepository;
use App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource\Pages\EditAfipMapucheSicossCalculo;
use App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource\Pages\ListAfipMapucheSicossCalculos;
use App\Filament\Afip\Resources\AfipMapucheSicossCalculoResource\Pages\ImportAfipMapucheSicossCalculo;

class AfipMapucheSicossCalculoResource extends Resource
{
    protected static ?string $model = AfipMapucheSicossCalculo::class;
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationGroup = 'AFIP';
    protected static ?string $navigationLabel = 'SICOSS Calculo';

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
                Tables\Filters\SelectFilter::make('caracter')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                ActionGroup::make([
                    Action::make('updateFromSicoss')
                    ->label('Actualizar desde SICOSS')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('¿Actualizar datos desde SICOSS?')
                    ->modalDescription('Esta acción actualizará los importes desde la tabla SICOSS')
                    ->modalSubmitActionLabel('Sí, actualizar')
                    ->action(fn () => app(AfipMapucheSicossCalculoRepository::class)->updateFromSicoss(date('Ym'))),

                Action::make('truncateTable')
                    ->label('Vaciar Tabla')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Vaciar tabla?')
                    ->modalDescription('Esta acción eliminará todos los registros de la tabla. Esta operación no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, vaciar tabla')
                    ->action(function() {
                        app(AfipMapucheSicossCalculoRepository::class)->truncate();
                        Notification::make()
                            ->success()
                            ->title('Tabla vaciada')
                            ->body('Se han eliminado todos los registros correctamente')
                            ->send();
                    }),

                    Action::make('updateUacadCaracter')
                        ->label('Actualizar UA/CAD y Carácter')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('¿Actualizar UA/CAD y Carácter?')
                        ->modalDescription('Esta acción actualizará los campos UA/CAD y Carácter desde Mapuche')
                        ->modalSubmitActionLabel('Sí, actualizar')
                        ->action(function () {
                            $result = app(AfipMapucheSicossCalculoUpdateService::class)
                                ->updateUacadAndCaracter();

                            if ($result['success']) {
                                Notification::make()
                                    ->success()
                                    ->title('Actualización exitosa')
                                    ->body($result['message'])
                                    ->send();
                            } else {
                                Notification::make()
                                    ->danger()
                                    ->title('Error en la actualización')
                                    ->body($result['message'])
                                    ->send();
                            }
                        })
                ])
                ->icon('heroicon-o-cog-8-tooth')
                ->tooltip('Acciones')
                ->size('lg'),
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

    public static function getActions(): array
    {
        return [
            Action::make('updateFromSicoss')
                ->label('Actualizar desde SICOSS')
                ->action(function () {
                    $service = app(AfipMapucheSicossCalculoUpdateService::class);
                    $result = $service->updateFromSicoss(date('Ym'));

                    if (empty($result['errors'])) {
                        Notification::make()
                            ->success()
                            ->title('Actualización exitosa')
                            ->body("Se actualizaron {$result['updated']} registros")
                            ->send();
                    } else {
                        Notification::make()
                            ->warning()
                            ->title('Actualización con errores')
                            ->body(implode("\n", $result['errors']))
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('¿Actualizar datos desde SICOSS?')
                ->modalDescription('Esta acción actualizará los importes desde la tabla SICOSS')
                ->modalSubmitActionLabel('Sí, actualizar')
                ->color('success')
                ->icon('heroicon-o-arrow-path')
        ];
    }
}
