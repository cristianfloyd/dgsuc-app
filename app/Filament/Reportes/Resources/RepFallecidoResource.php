<?php

namespace App\Filament\Reportes\Resources;

use Carbon\Carbon;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\RepFallecido;
use Filament\Resources\Resource;
use App\Exports\FallecidosExport;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Select;
use App\Exports\DosubaSinLiquidarExport;
use App\Services\FallecidosTableService;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use App\Filament\Reportes\Resources\RepFallecidoResource\Pages;

class RepFallecidoResource extends Resource
{
    protected static ?string $model = RepFallecido::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                Tables\Columns\TextColumn::make('nro_legaj')
                    ->label('Legajo')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('apellido')
                    ->label('Apellido')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cuil')
                    ->label('CUIL')
                    ->searchable(),
                Tables\Columns\TextColumn::make('codc_uacad')
                    ->label('Unidad Académica'),
                Tables\Columns\TextColumn::make('fec_defun')
                    ->label('Fecha Defunción')
                    ->date(),
            ])
            ->headerActions([
                ActionGroup::make([
                    Action::make('populate')
                        ->label('Poblar Tabla')
                        ->icon('heroicon-m-arrow-down-on-square')
                        ->color('success')
                        ->form([
                            DatePicker::make('fecha_desde')
                                ->label('Fecha Desde')
                                ->required()
                                ->default(now()->subMonth(2))
                                ->format('Y-m-d')
                                ->maxDate(now()),
                        ])
                        ->action(function (array $data): void {
                            $service = app(FallecidosTableService::class);
                            $service->populateFromDate($data['fecha_desde']);
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
                            $service = app(FallecidosTableService::class);
                            $service->truncateTable();
                            Notification::make()
                                ->title('Tabla limpiada exitosamente')
                                ->success()
                                ->send();
                        }),
                    Action::make('export')
                        ->label('Exportar a Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->form([
                            Select::make('periodo')
                                ->label('Período')
                                ->options(function() {
                                    $options = [];
                                    $date = Carbon::now();
                                    for ($i = 0; $i < 12; $i++) {
                                        $periodo = $date->format('Ym');
                                        $options[$periodo] = $date->format('Y/m');
                                        $date->subMonth();
                                    }
                                    return $options;
                                })
                                ->default(fn() => Carbon::now()->subMonth()->format('Ym'))
                                ->required()
                        ])
                        ->action(function (array $data) {
                            return Excel::download(
                                new FallecidosExport(
                                    records: RepFallecido::all(),
                                    periodo: $data['periodo']
                                ),
                                'fallecidos-' . $data['periodo'] . '.xlsx'
                            );
                        }),
                ])
                ->label('Acciones')
                ->icon('heroicon-m-cog-6-tooth'),
            ])
            ->defaultSort('nro_legaj', 'desc');
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
            'index' => Pages\ListRepFallecidos::route('/'),
            'create' => Pages\CreateRepFallecido::route('/create'),
            'edit' => Pages\EditRepFallecido::route('/{record}/edit'),
        ];
    }
}
