<?php

namespace App\Filament\Reportes\Resources;

use App\Exports\RepEmbarazadasExport;
use App\Filament\Reportes\Resources\RepEmbarazadaResource\Pages;
use App\Models\RepEmbarazada;
use App\Services\RepEmbarazadaService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class RepEmbarazadaResource extends Resource
{
    protected static ?string $model = RepEmbarazada::class;

    protected static ?string $label = 'Reporte Embarazadas';

    protected static ?string $navigationLabel = 'Reporte Embarazadas';

    protected static ?string $navigationGroup = 'Dosuba';
    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('apellido')
                    ->maxLength(20),
                Forms\Components\TextInput::make('nombre')
                    ->maxLength(20),
                Forms\Components\Textarea::make('cuil')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('codc_uacad')
                    ->maxLength(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nro_legaj')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('apellido')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codc_uacad')
                    ->searchable(),
            ])
            ->headerActions([
                ActionGroup::make([
                    Action::make('populate')
                        ->label('Actualizar Datos')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->action(function (): void {
                            $service = app(RepEmbarazadaService::class);
                            $service->populateTable();

                            Notification::make()
                                ->title('Datos actualizados correctamente')
                                ->success()
                                ->send();
                        }),
                    Action::make('truncate')
                        ->label('Vaciar Tabla')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (): void {
                            $service = app(RepEmbarazadaService::class);
                            $service->truncateTable();

                            Notification::make()
                                ->title('Tabla vaciada correctamente')
                                ->success()
                                ->send();
                        }),
                    Action::make('export')
                        ->label('Exportar a Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function () {
                            return Excel::download(
                                new RepEmbarazadasExport(),
                                'personal-embarazado-' . now()->format('Y-m-d') . '.xlsx',
                            );
                        }),
                ])->label('Acciones de Tabla')
                    ->icon('heroicon-o-cog'),
            ])
            ->filters([

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

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRepEmbarazadas::route('/'),
            // 'create' => Pages\CreateRepEmbarazada::route('/create'),
            'edit' => Pages\EditRepEmbarazada::route('/{record}/edit'),
        ];
    }
}
