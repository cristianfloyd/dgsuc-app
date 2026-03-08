<?php

namespace App\Filament\Afip\Resources\AfipMapucheMiSimplificacions;

use Filament\Schemas\Schema;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\Action;
use Exception;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use App\Filament\Afip\Resources\AfipMapucheMiSimplificacions\Pages\ListAfipMapucheMiSimplificacions;
use App\Filament\Afip\Resources\AfipMapucheMiSimplificacions\Pages\CreateAfipMapucheMiSimplificacion;
use App\Filament\Afip\Resources\AfipMapucheMiSimplificacions\Pages\EditAfipMapucheMiSimplificacion;
use App\Enums\EstadoCierre;
use App\Enums\PuestoDesempenado;
use App\Filament\Afip\Resources\AfipMapucheMiSimplificacionResource\Pages;
use App\Models\AfipMapucheMiSimplificacion;
use App\Services\AfipMapucheExportService;
use App\Services\AfipMapucheSicossService;
use App\Services\Mapuche\LiquidacionService;
use App\Services\MapucheMiSimplificacionService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;

class AfipMapucheMiSimplificacionResource extends Resource
{
    protected static ?string $model = AfipMapucheMiSimplificacion::class;

    protected static string | \UnitEnum | null $navigationGroup = 'AFIP';

    protected static ?string $navigationLabel = 'Mi Simplificación';

    protected static ?string $pluralNavigationLabel = 'Mi Simplificación';

    protected static ?string $label = 'Mi Simplificación';

    protected static ?string $pluralLabel = 'Mi Simplificación';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrow-right-circle';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //// TextInput::make('periodo_fiscal')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('periodo_fiscal')
                    ->label('Periodo Fiscal')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('nro_legaj')
                    ->label('Legajo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('cuil')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('inicio_rel_laboral')
                    ->label('Inicio Rel. Laboral')
                    ->sortable(),
                TextColumn::make('fin_rel_laboral')
                    ->label('Fin Rel. Laboral')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('domicilio'),
                TextColumn::make('actividad'),
                TextColumn::make('puesto')
                    ->badge()
                    ->label('Puesto')
                    ->colors([
                        'primary' => fn ($state) => $state === PuestoDesempenado::PROFESOR_UNIVERSITARIO->descripcion(),
                        'secondary' => fn ($state) => $state === PuestoDesempenado::PROFESOR_SECUNDARIO->descripcion(),
                        'warning' => fn ($state) => $state === PuestoDesempenado::DIRECTIVO->descripcion(),
                        'success' => fn ($state) => $state === PuestoDesempenado::NODOCENTE->descripcion(),
                    ]),
            ])
            ->filters([
                SelectFilter::make('sino_cerra')
                    ->options(EstadoCierre::asSelectArray()),
                Filter::make('periodo_fiscal')
                    ->schema([
                        TextInput::make('periodo_fiscal')
                            ->mask('999999')
                            ->label('Periodo Fiscal'),
                    ]),
            ])
            ->headerActions([
                Action::make('exportTxt')
                    ->label('Exportar TXT (AFIP)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (AfipMapucheExportService $afipMapucheExportService) {
                        try {
                            return $afipMapucheExportService->exportToTxt();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Error al exportar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->color('success'),
                Action::make('poblarMiSimplificacion')
                    ->label('Poblar Mi Simplificación')
                    ->schema([
                        Select::make('periodo_fiscal')
                            ->label('Período Fiscal')
                            ->options(fn (AfipMapucheSicossService $afipMapucheSicossService) => $afipMapucheSicossService->getPeriodosFiscalesForSelect())
                            ->required()
                            ->searchable(),
                        Select::make('nro_liqui')
                            ->label('Número de Liquidación')
                            ->options(function (callable $get, LiquidacionService $liquidacionService): array {
                                $periodoFiscal = $get('periodo_fiscal');
                                if (!$periodoFiscal) {
                                    return [];
                                }
                                $year = substr($periodoFiscal, 0, 4);
                                $month = substr($periodoFiscal, 4, 2);
                                $liquidaciones = $liquidacionService->getLiquidacionesForSelect($year, $month);

                                // Formatear el array para la vista
                                return collect($liquidaciones)->mapWithKeys(function ($descripcion, $nro_liqui) {
                                    return [$nro_liqui => "# {$nro_liqui} - {$descripcion}"];
                                })->toArray();
                            })
                            ->required()
                            ->searchable()
                            ->reactive(),
                    ])
                    ->action(function (array $data, MapucheMiSimplificacionService $mapucheMiSimplificacionService): void {
                        try {
                            $processedCount = $mapucheMiSimplificacionService->poblarMiSimplificacion(
                                (int) $data['periodo_fiscal'],
                                (int) $data['nro_liqui'],
                            );

                            if ($processedCount > 0) {
                                Notification::make()
                                    ->title('Proceso completado')
                                    ->body("Se han procesado {$processedCount} registros.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('No se encontraron CUILs para procesar.')
                                    ->body('No se encontraron CUILs para procesar.')
                                    ->danger()
                                    ->send();
                            }
                        } catch (Exception $e) {
                            Log::error('Error al poblar Mi Simplificación', [
                                'mensaje' => $e->getMessage(),
                                'traza' => $e->getTraceAsString(),
                                'datos' => $data,
                            ]);

                            Notification::make()
                                ->danger()
                                ->title('Error en el proceso')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->icon('heroicon-o-arrow-path')
                    ->color('success'),
            ])
            ->recordActions([
                EditAction::make(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('periodo_fiscal', 'desc');
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAfipMapucheMiSimplificacions::route('/'),
            'create' => CreateAfipMapucheMiSimplificacion::route('/create'),
            'edit' => EditAfipMapucheMiSimplificacion::route('/{record}/edit'),
        ];
    }
}
