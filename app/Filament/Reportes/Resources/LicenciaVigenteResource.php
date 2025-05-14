<?php

namespace App\Filament\Reportes\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\LicenciaVigente;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Mapuche\MapucheConfig;
use App\Services\DateFormatterService;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use App\Services\Mapuche\LicenciaService;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Data\Responses\LicenciaVigenteData;
use Illuminate\Database\Eloquent\Collection;
use App\Services\Excel\Exports\LicenciasVigentesExport;
use App\Filament\Reportes\Resources\LicenciaVigenteResource\Pages;

class LicenciaVigenteResource extends Resource
{
    protected static ?string $model = LicenciaVigente::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Licencias';
    protected static ?int $navigationSort = 100;
    protected static ?string $slug = 'licencias-vigentes';

    /**
     * Obtiene una query base de Eloquent.
     */
    public static function getEloquentQuery(): Builder
    {
        $legajos = session('licencias_vigentes_legajos', []);
        $sessionId = session()->getId();

        // Cargar licencias y obtener una query que filtra por la sesión actual
        return LicenciaVigente::cargarLicenciasVigentes($legajos, $sessionId);
    }

    public static function getNavigationLabel(): string
    {
        return 'Licencias Vigentes';
    }

    public static function getPluralLabel(): string
    {
        return 'Licencias Vigentes';
    }

    public static function getNavigationBadge(): ?string
    {
        return null;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * Sobrescribe el método para proporcionar nuestra implementación personalizada
     * que no depende de un modelo Eloquent.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->heading('Licencias Vigentes del Período')
            ->description('Muestra las licencias vigentes de los agentes para el periodo fiscal actual')
            ->columns([
                TextColumn::make('nro_legaj')
                    ->label('Legajo')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('descripcion_licencia')
                    ->label('Descripción')
                    ->limit(50)
                    ->tooltip(fn(?LicenciaVigente $record) => $record?->descripcion_licencia ?? '')
                    ->sortable(),

                TextColumn::make('condicion')
                    ->label('Código')
                    ->sortable()
                    ->badge()
                    ->formatStateUsing(fn (?LicenciaVigente $record) => $record?->descripcion_condicion ?? '')
                    ->color(fn (?LicenciaVigente $record): string => $record ? match($record->condicion) {
                        5, 11 => 'info', // Maternidad
                        10 => 'warning', // Excedencia
                        12 => 'success', // Vacaciones
                        18, 19 => 'danger', // ILT
                        51 => 'indigo', // Protección Integral
                        default => 'gray',
                    } : 'gray'),

                TextColumn::make('inicio')
                    ->label('Inicio')
                    ->sortable(),

                TextColumn::make('final')
                    ->label('Fin')
                    ->sortable(),

                TextColumn::make('dias_totales')
                    ->label('Días')
                    ->sortable(),

                TextColumn::make('fecha_desde')
                    ->label('Fecha Desde')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('fecha_hasta')
                    ->label('Fecha Hasta')
                    ->date('d/m/Y')
                    ->formatStateUsing(fn ($state) => DateFormatterService::formatOrDefault($state))
                    ->sortable(),

                TextColumn::make('es_legajo')
                    ->label('Tipo')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Legajo' : 'Cargo')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                    ->sortable(),

                TextColumn::make('nro_cargo')
                    ->label('Cargo')
                    ->visible(fn (?LicenciaVigente $record): bool => $record ? !$record->es_legajo : false)
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('condicion')
                    ->label('Tipo de Licencia')
                    ->options([
                        5 => 'Maternidad',
                        10 => 'Excedencia',
                        11 => 'Maternidad Down',
                        12 => 'Vacaciones',
                        13 => 'Licencia Sin Goce de Haberes',
                        18 => 'ILT Primer Tramo',
                        19 => 'ILT Segundo Tramo',
                        51 => 'Protección Integral',
                    ]),

                SelectFilter::make('es_legajo')
                    ->label('Tipo de Asociación')
                    ->options([
                        true => 'Legajo',
                        false => 'Cargo',
                    ]),
            ])
            ->headerActions([
                Action::make('consultar_todas_licencias')
                    ->label('Consultar Todas Las Licencias')
                    ->icon('heroicon-o-document-check')
                    ->color('success')
                    ->tooltip('Consulta todas las licencias vigentes en el período actual')
                    ->action(function (Tables\Actions\Action $action): void {
                        // Obtener todos los legajos con licencias vigentes desde el servicio
                        $licenciaService = app(LicenciaService::class);
                        $legajos = $licenciaService->getLegajosConLicenciasVigentes();

                        if (empty($legajos)) {
                            $action->failure('No se encontraron licencias vigentes en el período actual');
                            return;
                        }

                        // Guardar en sesión para poder utilizarlo en el método getEloquentQuery
                        session(['licencias_vigentes_legajos' => $legajos]);

                        $action->success('Se han cargado ' . count($legajos) . ' legajos con licencias vigentes');
                    }),

                Action::make('consultar_legajos')
                    ->label('Consultar Legajos')
                    ->icon('heroicon-o-magnifying-glass')
                    ->form([
                        TextInput::make('legajos')
                            ->label('Legajos (separados por coma)')
                            ->placeholder('Ej: 1234,5678')
                            ->required(),
                    ])
                    ->action(function (array $data, Tables\Actions\Action $action): void {
                        $legajos = array_map('trim', explode(',', $data['legajos']));

                        // Guardar en sesión para poder utilizarlo en el método getEloquentQuery
                        session(['licencias_vigentes_legajos' => $legajos]);

                        $action->success('Licencias consultadas correctamente');
                    }),

                Tables\Actions\Action::make('exportar_excel')
                    ->label('Exportar Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Tables\Actions\Action $action) {
                        $legajos = session('licencias_vigentes_legajos', []);

                        if (empty($legajos)) {
                            $action->failure('No hay legajos seleccionados para exportar');
                            return;
                        }

                        $licenciaService = app(LicenciaService::class);
                        $licencias = $licenciaService->getLicenciasVigentes($legajos);

                        $periodo = MapucheConfig::getAnioFiscal() . '-' . MapucheConfig::getMesFiscal();
                        $nombreArchivo = 'licencias_vigentes_' . $periodo . '.xlsx';

                        return Excel::download(
                            new LicenciasVigentesExport($licencias, $periodo),
                            $nombreArchivo
                        );
                    }),
            ])
            ->actions([
                // No necesitamos acciones de edición ya que son solo datos de consulta
            ])
            ->bulkActions([
                // No necesitamos acciones masivas
            ])
            ->paginated([5,10, 25, 50, 100])
            ->defaultPaginationPageOption(5);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        // Usamos las páginas generadas automáticamente por Filament pero personalizamos el comportamiento
        return [
            'index' => Pages\ListLicenciaVigentes::route('/'),
        ];
    }
}
