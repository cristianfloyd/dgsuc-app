<?php

namespace App\Filament\Afip\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Enums\PuestoDesempenado;
use Filament\Resources\Resource;
use App\Models\AfipMapucheSicoss;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\CuilRepository;
use App\Models\AfipRelacionesActivas;
use Filament\Forms\Components\Select;
use App\Traits\MapucheConnectionTrait;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Services\AfipMapucheExportService;
use App\Models\AfipMapucheMiSimplificacion;
use App\Services\Mapuche\LiquidacionService;
use Filament\Tables\Actions\Action as TableAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Afip\Resources\AfipMapucheMiSimplificacionResource\Pages;
use App\Filament\Afip\Resources\AfipMapucheMiSimplificacionResource\RelationManagers;

class AfipMapucheMiSimplificacionResource extends Resource
{
    use MapucheConnectionTrait;
    protected static ?string $model = AfipMapucheMiSimplificacion::class;
    protected static ?string $navigationGroup = 'AFIP';
    protected static ?string $navigationLabel = 'Mi Simplificación';
    protected static ?string $pluralNavigationLabel = 'Mi Simplificación';
    protected static ?string $label = 'Mi Simplificación';
    protected static ?string $pluralLabel = 'Mi Simplificación';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-circle';
    protected static ?int $navigationSort = 3;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                        'primary' => fn($state) => $state === PuestoDesempenado::PROFESOR_UNIVERSITARIO->descripcion(),
                        'secondary' => fn($state) => $state === PuestoDesempenado::PROFESOR_SECUNDARIO->descripcion(),
                        'warning' => fn($state) => $state === PuestoDesempenado::DIRECTIVO->descripcion(),
                        'success' => fn($state) => $state === PuestoDesempenado::NODOCENTE->descripcion(),
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sino_cerra')
                    ->options([
                        'S' => 'Cerrado',
                        'N' => 'Abierto',
                    ]),
                Tables\Filters\Filter::make('periodo_fiscal')
                    ->form([
                        TextInput::make('periodo_fiscal')
                            ->mask('999999')
                            ->label('Periodo Fiscal'),
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportTxt')
                    ->label('Exportar TXT (AFIP)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (AfipMapucheExportService $afipMapucheExportService) {
                        try {
                            return $afipMapucheExportService->exportToTxt();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al exportar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->color('success'),
                TableAction::make('poblarMiSimplificacion')
                    ->label('Poblar Mi Simplificación')
                    ->form([
                        Select::make('periodo_fiscal')
                            ->label('Período Fiscal')
                            ->options(function () {
                                return AfipMapucheSicoss::distinct()
                                    ->pluck('periodo_fiscal', 'periodo_fiscal')
                                    ->toArray();
                            })
                            ->required()
                            ->searchable(),
                        Select::make('nro_liqui')
                            ->label('Número de Liquidación')
                            ->options(function (callable $get, LiquidacionService $liquidacionService): array {
                                $periodoFiscal = $get('periodo_fiscal');
                                if (!$periodoFiscal){ return [];}
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
                            ->reactive()
                    ])
                    ->action(function (array $data, CuilRepository $cuilRepository) {
                        try {
                            // Iniciar una transacción para asegurar la integridad de los datos
                            DB::connection(self::getStaticConnectionName())->beginTransaction();

                            // Obtener CUILs que no están en RelacionesActivas
                            $cuils = $cuilRepository->getCuilsNotInAfip($data['periodo_fiscal']);

                            if ($cuils->isEmpty()) {
                                Notification::make()
                                    ->warning()
                                    ->title('No hay CUILs para procesar')
                                    ->send();
                                DB::rollBack();
                                return;
                            }

                            // Utilizar el método del modelo para ejecutar la función almacenada
                            $resultado = AfipMapucheMiSimplificacion::mapucheMiSimplificacion(
                                $data['nro_liqui'],
                                $data['periodo_fiscal']
                            );

                            if (!$resultado) {
                                throw new \Exception('Error al ejecutar la función almacenada');
                            }

                            DB::connection(self::getStaticConnectionName())->commit();

                            Notification::make()
                                ->success()
                                ->title('Proceso completado')
                                ->body('Se han procesado ' . $cuils->count() . ' registros.')
                                ->send();

                        } catch (\Exception $e) {
                            DB::connection(self::getStaticConnectionName())->rollBack();

                            // Registrar el error para diagnóstico
                            Log::error('Error al poblar Mi Simplificación', [
                                'mensaje' => $e->getMessage(),
                                'traza' => $e->getTraceAsString(),
                                'datos' => $data
                            ]);

                            Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('periodo_fiscal', 'desc');
    }

    public static function getStaticConnectionName(): string
    {
        $instance = new static;
        return $instance->getConnectionName();
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
            'index' => Pages\ListAfipMapucheMiSimplificacions::route('/'),
            'create' => Pages\CreateAfipMapucheMiSimplificacion::route('/create'),
            'edit' => Pages\EditAfipMapucheMiSimplificacion::route('/{record}/edit'),
        ];
    }

    // Agregar este método para manejar la consulta
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select('*')
            ->addSelect(DB::raw('puesto as puesto_raw')); // Agregamos el campo puesto sin cast
    }
}
