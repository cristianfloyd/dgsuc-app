<?php

namespace App\Filament\Reportes\Resources\RepFallecidos\RepFallecidos;

use App\Exports\FallecidosExport;
use App\Filament\Reportes\Resources\RepFallecidos\Pages\CreateRepFallecido;
use App\Filament\Reportes\Resources\RepFallecidos\Pages\EditRepFallecido;
use App\Filament\Reportes\Resources\RepFallecidos\Pages\ListRepFallecidos;
use App\Models\RepFallecido;
use App\Services\FallecidosTableService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class RepFallecidoResource extends Resource
{
    protected static ?string $model = RepFallecido::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string|UnitEnum|null $navigationGroup = 'Dosuba';

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

            ]);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_legaj')
                    ->label('Legajo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('apellido')
                    ->label('Apellido')
                    ->searchable(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('cuil')
                    ->label('CUIL')
                    ->searchable(),
                TextColumn::make('codc_uacad')
                    ->label('Unidad Académica'),
                TextColumn::make('fec_defun')
                    ->label('Fecha Defunción')
                    ->date(),
            ])
            ->headerActions([
                ActionGroup::make([
                    Action::make('populate')
                        ->label('Poblar Tabla')
                        ->icon('heroicon-m-arrow-down-on-square')
                        ->color('success')
                        ->schema([
                            DatePicker::make('fecha_desde')
                                ->label('Fecha Desde')
                                ->required()
                                ->default(now()->subMonth())
                                ->format('Y-m-d')
                                ->maxDate(now()),
                        ])
                        ->action(function (array $data): void {
                            $fallecidosTableService = resolve(FallecidosTableService::class);
                            $fallecidosTableService->populateFromDate($data['fecha_desde']);
                            Notification::make()
                                ->title('Tabla poblada exitosamente')
                                ->success()
                                ->send();
                        }),
                    Action::make('truncate')
                        ->label('Limpiar Tabla')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('¿Está seguro de limpiar la tabla?')
                        ->modalDescription('Esta acción eliminará todos los registros de la tabla.')
                        ->modalSubmitActionLabel('Sí, limpiar tabla')
                        ->action(function (): void {
                            $fallecidosTableService = resolve(FallecidosTableService::class);
                            $fallecidosTableService->truncateTable();
                            Notification::make()
                                ->title('Tabla limpiada exitosamente')
                                ->success()
                                ->send();
                        }),
                    Action::make('export')
                        ->label('Exportar a Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->schema([
                            Select::make('periodo')
                                ->label('Período')
                                ->options(function (): array {
                                    $options = [];
                                    $date = \Illuminate\Support\Facades\Date::now();
                                    for ($i = 0; $i < 12; $i++) {
                                        $periodo = $date->format('Ym');
                                        $options[$periodo] = $date->format('Y/m');
                                        $date->subMonth();
                                    }
                                    return $options;
                                })
                                ->default(fn() => \Illuminate\Support\Facades\Date::now()->subMonth()->format('Ym'))
                                ->required(),
                        ])
                        ->action(fn(array $data) => Excel::download(
                            new FallecidosExport(
                                records: RepFallecido::all(),
                                periodo: $data['periodo'],
                            ),
                            'fallecidos-' . $data['periodo'] . '.xlsx',
                        )),
                ])
                    ->label('Acciones')
                    ->icon('heroicon-m-cog-6-tooth'),
            ])
            ->defaultSort('nro_legaj', 'desc');
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [

        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListRepFallecidos::route('/'),
            'create' => CreateRepFallecido::route('/create'),
            'edit' => EditRepFallecido::route('/{record}/edit'),
        ];
    }
}
