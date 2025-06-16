<?php

namespace App\Filament\Afip\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Mapuche\Dh22;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use App\Models\AfipMapucheSicoss;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use App\Services\SicossExportService;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Actions\PoblarAfipArtAction;
use App\Services\Mapuche\PeriodoFiscalService;
use App\Services\Mapuche\TableSelectorService;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Afip\SicossEmbarazadasService;
use Filament\Tables\Actions\Action as TableAction;
use App\Traits\FilamentAfipMapucheSicossTableTrait;
use App\Filament\Afip\Resources\AfipMapucheSicossResource\Pages;


class AfipMapucheSicossResource extends Resource
{
    use FilamentAfipMapucheSicossTableTrait;

    protected static ?string $model = AfipMapucheSicoss::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-left-circle';
    protected static ?string $navigationGroup = 'AFIP';
    protected static ?string $navigationLabel = 'Mapuche SICOSS';
    protected static ?string $pluralLabel = 'Mapuche SICOSS';
    protected static ?int $navigationSort = 0;





    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periodo_fiscal')
                    ->label('Período Fiscal')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('dh01.nro_legaj')
                    ->label('Legajo')
                    ,
                TextColumn::make('cuil')
                    ->label('CUIL')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('apnom')
                    ->label('Apellido y Nombre')
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cod_act')
                        ->label('Cod. Act.')
                        ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('cod_cond')
                    ->label('Condición')
                    ->toggleable(isToggledHiddenByDefault: false),
                IconColumn::make('conyuge')
                    ->label('Cónyuge')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cant_hijos')
                    ->label('Cant. Hijos')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cod_situacion')
                    ->label('Situación')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rem_impo1')
                    ->label('Rem impo 1')
                    ->money('ARS'),
                TextColumn::make('rem_total')
                    ->label('Rem Total')
                    ->money('ARS')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rem_impo4')
                    ->label('Rem impo 4')
                    ->money('ARS')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rem_impo6')
                    ->label('Rem impo 6')
                    ->money('ARS'),
                TextColumn::make('rem_imp7')
                    ->label('Rem impo 7')
                    ->money('ARS'),
                TextColumn::make('sac')
                    ->label('SAC')
                    ->money('ARS'),
                TextColumn::make('diferencia_rem')
                    ->label('Diferencia Rem')
                    ->money('ARS'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('periodo_fiscal')
                    ->label('Período Fiscal')
                    ->options(function () {
                        return AfipMapucheSicoss::distinct()
                            ->pluck('periodo_fiscal', 'periodo_fiscal')
                            ->toArray();
                    })
                    ->searchable()
            ])
            ->headerActions([
                ActionGroup::make([
                    Action::make('actualizarEmbarazadas')
                        ->label('Actualizar Embarazadas')
                        ->icon('heroicon-o-user-group')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Actualizar Situación de Embarazadas')
                        ->modalDescription('¿Está seguro que desea actualizar la situación de revista de embarazadas para el período fiscal actual? Este proceso actualizará los códigos de situación de revista para las agentes con licencia por embarazo.')
                        ->action(function (PeriodoFiscalService $periodoFiscalService, TableSelectorService $tableSelectorService) {
                            $sicossEmbarazadasService = new SicossEmbarazadasService($tableSelectorService, $periodoFiscalService);

                            $periodoFiscal = $periodoFiscalService->getPeriodoFiscal();
                            $year = $periodoFiscal['year'];
                            $month = $periodoFiscal['month'];

                            // Obtener liquidaciones válidas
                            $liquidaciones = Dh22::filterByYearMonth($year, $month)
                                ->generaImpositivo()
                                ->definitiva()
                                ->pluck('nro_liqui')
                                ->toArray();

                            if (empty($liquidaciones)) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Sin liquidaciones')
                                    ->warning()
                                    ->body("No se encontraron liquidaciones que generen datos impositivos para el período {$year}-{$month}")
                                    ->send();
                                return;
                            }

                            $resultado = $sicossEmbarazadasService->actualizarEmbarazadas([
                                'year' => $year,
                                'month' => $month,
                                'liquidaciones' => $liquidaciones,
                                'nro_liqui' => $liquidaciones[0]
                            ]);

                            if ($resultado['status'] === 'success') {
                                \Filament\Notifications\Notification::make()
                                    ->title('Actualización de embarazadas completada')
                                    ->success()
                                    ->body($resultado['message'])
                                    ->send();
                            } elseif ($resultado['status'] === 'warning') {
                                \Filament\Notifications\Notification::make()
                                    ->title('Advertencia')
                                    ->warning()
                                    ->body($resultado['message'])
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error en la actualización')
                                    ->danger()
                                    ->body($resultado['message'])
                                    ->send();
                            }
                        }),
                    // Action::make('exportarSicossTxt')
                    //     ->label('Exportar TXT SICOSS')
                    //     ->icon('heroicon-o-document-arrow-down')
                    //     ->action(function () {
                    //         $exportService = Container::getInstance()->make(SicossExportService::class);
                    //         $path = $exportService->generarArchivoTxt(static::getModel()::all());
                    //         return response()->download($path)->deleteFileAfterSend();
                    //     })
                    //     ->color('success'),
                    Action::make('exportarSicossExcel')
                        ->label('Exportar Excel')
                        ->icon('heroicon-o-table-cells')
                        ->action(function () {
                            $exportService = Container::getInstance()->make(SicossExportService::class);
                            $path = $exportService->generarArchivoExcel(static::getModel()::all());
                            return response()->download($path)->deleteFileAfterSend();
                        })
                        ->color('success'),
                    Action::make('exportarAvanzado')
                        ->label('Exportación Avanzada')
                        ->url(fn(): string => self::getUrl('export'))
                        ->color('success')
                        ->icon('heroicon-o-adjustments-horizontal'),
                    Action::make('importar')
                        ->label('Importar')
                        ->url(fn(): string => self::getUrl('import'))
                        ->color('success')
                        ->icon('heroicon-o-arrow-up-tray'),
                    PoblarAfipArtAction::make('poblar_art')
                        ->label('Poblar ART')
                        ->modalHeading('Confirmación de Poblado ART')
                        ->modalDescription('
                                **¡Importante!** Esta acción:

                                - Utilizará los datos del período fiscal actual para poblar la tabla ART.
                                - El proceso puede tomar varios minutos dependiendo de la cantidad de registros.
                                - Se recomienda verificar que los datos de SICOSS estén completos antes de proceder.
                            ')
                        ->modalSubmitActionLabel('Sí, poblar ART')
                        ->modalIcon('heroicon-o-arrow-up-tray')
                        ->color('success')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->requiresConfirmation()
                        ->slideOver(),
                    Action::make('exportarFiltradosTxt')
                        ->label('Exportar Filtrados (TXT)')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn($livewire) => static::exportarRegistrosFiltrados($livewire, 'txt'))
                        ->color('success'),
                    Action::make('exportarFiltradosExcel')
                        ->label('Exportar Filtrados (Excel)')
                        ->icon('heroicon-o-table-cells')
                        ->action(fn($livewire) => static::exportarRegistrosFiltrados($livewire, 'excel'))
                        ->color('success'),
                ])
                    ->icon('heroicon-o-cog-8-tooth')
                    ->tooltip('Acciones')
                    ->size('lg'),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('exportarSeleccionadosTxt')
                        ->label('Exportar Seleccionados (TXT)')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(fn(Collection $records) => static::exportarRegistros($records, 'txt'))
                        ->color('success'),
                    Tables\Actions\BulkAction::make('exportarSeleccionadosExcel')
                        ->label('Exportar Seleccionados (Excel)')
                        ->icon('heroicon-o-table-cells')
                        ->action(fn(Collection $records) => static::exportarRegistros($records, 'excel'))
                        ->color('success')
                ]),
            ])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No se encontraron registros')
            ->emptyStateDescription('Utiliza el botón importar para cargar datos desde un archivo.')
            ->emptyStateActions([
                TableAction::make('importar')
                    ->label('Importar')
                    ->url(fn(): string => self::getUrl('import'))
                    ->color('success')
                    ->icon('heroicon-o-arrow-up-tray')
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getActions(): array
    {
        return [
            PoblarAfipArtAction::make()
                ->label('Poblar ART')
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray'),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAfipMapucheSicosses::route('/'),
            'create' => Pages\CreateAfipMapucheSicoss::route('/create'),
            'import' => Pages\ImportAfipMapucheSicoss::route('/import'),
            'export' => Pages\ExportAfipMapucheSicoss::route('/export'),
        ];
    }

    /**
     * Exporta los registros filtrados a un archivo.
     *
     * Esta función utiliza el query builder de Livewire para obtener los registros filtrados
     * y luego llama a la función exportarRegistros para generar el archivo de exportación.
     *
     * @param $livewire El objeto Livewire que contiene el query builder filtrado.
     * @param string $formato El formato de exportación ('txt' o 'excel').
     * @return Response El objeto Response que contiene el archivo de exportación.
     */
    protected static function exportarRegistrosFiltrados($livewire, string $formato = 'txt'): Response
    {
        $registrosFiltrados = $livewire->getFilteredTableQuery()->get();
        return static::exportarRegistros($registrosFiltrados, $formato);
    }

    /**
     * Exporta los registros proporcionados a un archivo.
     *
     * @param Collection $registros Los registros a exportar.
     * @param string $formato El formato de exportación ('txt' o 'excel').
     * @return Response El objeto Response que contiene el archivo de exportación.
     */
    protected static function exportarRegistros(Collection $registros, string $formato = 'txt'): Response
    {
        // Obtener la instancia del servicio desde el contenedor
        $exportService = Container::getInstance()->make(SicossExportService::class);

        // Obtener el período fiscal del primer registro o usar el actual
        $periodoFiscal = $registros->isNotEmpty()
            ? $registros->first()->periodo_fiscal
            : date('Ym');

        // Generar el archivo en el formato especificado
        $path = $exportService->generarArchivo($registros, $formato, $periodoFiscal);

        return response()->download($path)->deleteFileAfterSend();
    }

    /**
     * Genera un archivo SICOSS con los datos de los registros proporcionados.
     *
     * Esta función toma una colección de registros y genera un archivo de texto con el formato
     * requerido por el sistema SICOSS. El archivo se guarda en el directorio temporal y se
     * devuelve la ruta completa del archivo generado.
     *
     * @param ?Collection $registros Una colección de registros a incluir en el archivo SICOSS.
     *                               Si no se proporciona, se utilizarán todos los registros.
     * @return string La ruta completa del archivo SICOSS generado.
     */
    public static function generarArchivoSicoss(?Collection $registros = null): string
    {
        $registros ??= static::getModel()::all();
        $contenido = '';

        // Función auxiliar para formatear decimales con 2 decimales
        $formatearDecimal = function ($valor, $longitud) {
            $valor = $valor ?? 0;
            // Formatea el número con 2 decimales y punto como separador
            $numeroFormateado = number_format($valor, 2, '.', '');
            // Rellena con ceros a la izquierda hasta alcanzar la longitud deseada
            return str_pad($numeroFormateado, $longitud, '0', STR_PAD_LEFT);
        };

        // Nueva función para manejar strings con caracteres especiales
        $formatearString = function ($valor, $longitud) {
            $valor = $valor ?? '';
            // Convertir a ISO-8859-1 (Latin1) para manejar acentos
            $valor = mb_convert_encoding($valor, 'ISO-8859-1', 'UTF-8');
            // Limitar la longitud considerando caracteres especiales
            $valor = substr($valor, 0, $longitud);
            // Rellenar con espacios hasta alcanzar la longitud exacta
            return str_pad($valor, $longitud, ' ', STR_PAD_RIGHT);
        };

        foreach ($registros as $registro) {
            $linea = '';

            // Datos de identificación personal
            $linea .= str_pad($registro->cuil ?? '0', 11, '0', STR_PAD_LEFT);
            $linea .= $formatearString($registro->apnom, 30);;

            // Datos familiares
            $linea .= $registro->conyuge ? '1' : '0';
            $linea .= str_pad($registro->cant_hijos ?? '0', 2, '0', STR_PAD_LEFT);

            // Datos situación laboral
            $linea .= str_pad($registro->cod_situacion ?? '0', 2, '0', STR_PAD_LEFT);
            $linea .= str_pad($registro->cod_cond ?? '0', 2, '0', STR_PAD_LEFT);
            $linea .= str_pad($registro->cod_act ?? '0', 3, '0', STR_PAD_LEFT);
            $linea .= str_pad($registro->cod_zona ?? '0', 2, '0', STR_PAD_LEFT);

            // Datos aportes y obra social

            $linea .= $formatearDecimal($registro->porc_aporte, 5);
            $linea .= str_pad($registro->cod_mod_cont ?? '0', 3, '0', STR_PAD_LEFT);
            $linea .= str_pad($registro->cod_os ?? '0', 6, '0', STR_PAD_LEFT);
            $linea .= str_pad($registro->cant_adh ?? '0', 2, '0', STR_PAD_LEFT);

            // Remuneraciones principales
            $linea .= $formatearDecimal($registro->rem_total, 12);
            $linea .= $formatearDecimal($registro->rem_impo1, 12);
            $linea .= $formatearDecimal($registro->asig_fam_pag, 9);
            $linea .= $formatearDecimal($registro->aporte_vol, 9);
            $linea .= $formatearDecimal($registro->imp_adic_os, 9);
            $linea .= $formatearDecimal($registro->exc_aport_ss, 9);
            $linea .= $formatearDecimal($registro->exc_aport_os, 9);
            $linea .= str_pad($registro->prov ?? 'CABA', 50, ' ', STR_PAD_RIGHT);

            // Remuneraciones adicionales
            $linea .= $formatearDecimal($registro->rem_impo2, 12);
            $linea .= $formatearDecimal($registro->rem_impo3, 12);
            $linea .= $formatearDecimal($registro->rem_impo4, 12);

            // Datos siniestros y tipo empresa
            $linea .= str_pad($registro->cod_siniestrado ?? '0', 2, '0', STR_PAD_LEFT);
            $linea .= $registro->marca_reduccion ? '1' : '0';
            $linea .= $formatearDecimal($registro->recomp_lrt, 9);
            $linea .= $registro->tipo_empresa ?? '0';
            $linea .= $formatearDecimal($registro->aporte_adic_os, 9);
            $linea .= $registro->regimen ?? '0';

            // Situaciones de revista
            $linea .= str_pad($registro->sit_rev1 ?? '0', 2, '0', STR_PAD_LEFT);
            $linea .= str_pad($registro->dia_ini_sit_rev1 ?? '0', 2, '0', STR_PAD_LEFT);
            $linea .= str_pad($registro->sit_rev2 ?? '0', 2, '0', STR_PAD_LEFT);
            $linea .= str_pad($registro->dia_ini_sit_rev2 ?? '0', 2, '0', STR_PAD_LEFT);
            $linea .= str_pad($registro->sit_rev3 ?? '0', 2, '0', STR_PAD_LEFT);
            $linea .= str_pad($registro->dia_ini_sit_rev3 ?? '0', 2, '0', STR_PAD_LEFT);

            // Conceptos salariales
            $linea .= $formatearDecimal($registro->sueldo_adicc, 12);
            $linea .= $formatearDecimal($registro->sac, 12);
            $linea .= $formatearDecimal($registro->horas_extras, 12);
            $linea .= $formatearDecimal($registro->zona_desfav, 12);
            $linea .= $formatearDecimal($registro->vacaciones, 12);

            // Datos laborales
            $linea .= str_pad($registro->cant_dias_trab ?? '0', 9, '0', STR_PAD_LEFT);
            $linea .= $formatearDecimal($registro->rem_impo5, 12);
            $linea .= $registro->convencionado ? '1' : '0';
            $linea .= $formatearDecimal($registro->rem_impo6, 12);
            $linea .= $registro->tipo_oper ?? '0';

            // Conceptos adicionales
            $linea .= $formatearDecimal($registro->adicionales, 12);
            $linea .= $formatearDecimal($registro->premios, 12);
            $linea .= $formatearDecimal($registro->rem_dec_788, 12);
            $linea .= $formatearDecimal($registro->rem_imp7, 12);
            $linea .= str_pad($registro->nro_horas_ext ?? '0', 3, '0', STR_PAD_LEFT);
            $linea .= $formatearDecimal($registro->cpto_no_remun, 12);

            // Conceptos especiales
            $linea .= $formatearDecimal($registro->maternidad, 12);
            $linea .= $formatearDecimal($registro->rectificacion_remun, 9);
            $linea .= $formatearDecimal($registro->rem_imp9, 12);
            $linea .= $formatearDecimal($registro->contrib_dif, 9);

            // Datos finales
            $linea .= str_pad($registro->hstrab ?? '0', 3, '0', STR_PAD_LEFT);
            $linea .= $registro->seguro ? '1' : '0';
            $linea .= $formatearDecimal($registro->ley, 12);
            $linea .= $formatearDecimal($registro->incsalarial, 12);
            $linea .= $formatearDecimal($registro->remimp11, 12);

            // Asegurar que la línea tenga exactamente 500 caracteres
            if (strlen($linea) > 500) {
                $linea = substr($linea, 0, 500);
            } else {
                $linea = str_pad($linea, 500, ' ', STR_PAD_RIGHT);
            }

            $contenido .= $linea . PHP_EOL;
        }

        $nombreArchivo = 'SICOSS_' . date('Ym') . '.txt';
        Storage::disk('local')->put('tmp/' . $nombreArchivo, $contenido);
        return storage_path('app/tmp/' . $nombreArchivo);
    }
}
