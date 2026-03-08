<?php

namespace App\Filament\Reportes\Resources\RepEmbarazadas\RepEmbarazadas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Reportes\Resources\RepEmbarazadas\Pages\ListRepEmbarazadas;
use App\Filament\Reportes\Resources\RepEmbarazadas\Pages\EditRepEmbarazada;
use App\Exports\RepEmbarazadasExport;
use App\Filament\Reportes\Resources\RepEmbarazadaResource\Pages;
use App\Models\RepEmbarazada;
use App\Services\RepEmbarazadaService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class RepEmbarazadaResource extends Resource
{
    protected static ?string $model = RepEmbarazada::class;

    protected static ?string $label = 'Reporte Embarazadas';

    protected static ?string $navigationLabel = 'Reporte Embarazadas';

    protected static string | \UnitEnum | null $navigationGroup = 'Dosuba';
    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('apellido')
                    ->maxLength(20),
                TextInput::make('nombre')
                    ->maxLength(20),
                Textarea::make('cuil')
                    ->columnSpanFull(),
                TextInput::make('codc_uacad')
                    ->maxLength(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_legaj')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('apellido')
                    ->searchable(),
                TextColumn::make('nombre')
                    ->searchable(),
                TextColumn::make('codc_uacad')
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
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => ListRepEmbarazadas::route('/'),
            // 'create' => Pages\CreateRepEmbarazada::route('/create'),
            'edit' => EditRepEmbarazada::route('/{record}/edit'),
        ];
    }
}
