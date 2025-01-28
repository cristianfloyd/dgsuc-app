<?php

namespace App\Filament\Afip\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use App\Models\AfipMapucheSicoss;
use Illuminate\Support\Collection;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\FilamentTableInitializationTrait;
use App\Traits\FilamentAfipMapucheSicossTableTrait;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Afip\Resources\AfipMapucheSicossResource\Pages;
use App\Contracts\TableService\AfipMapucheSicossTableServiceInterface;
use App\Filament\Afip\Resources\AfipMapucheSicossResource\RelationManagers;

class AfipMapucheSicossResource extends Resource
{
    use FilamentAfipMapucheSicossTableTrait;

    protected static ?string $model = AfipMapucheSicoss::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'AFIP';
    protected static ?string $navigationLabel = 'Controles Sicoss';
    protected static ?string $pluralLabel = 'Controles Sicoss';




    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('periodo_fiscal')
                    ->label('Período Fiscal')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('cuil')
                    ->label('CUIL')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('apnom')
                    ->label('Apellido y Nombre')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                TextColumn::make('cod_cond')
                    ->label('Condición')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rem_total')
                    ->label('Rem Total')
                    ->money('ARS'),
                TextColumn::make('rem_impo6')
                    ->label('Rem impo 6')
                    ->money('ARS'),
                TextColumn::make('diferencia_rem')
                    ->label('Diferencia Rem')
                    ->money('ARS'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportarFiltrados')
                    ->label('Exportar Filtrados')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $registros = static::getModel()::query()->get();
                        $path = static::generarArchivoSicoss($registros);
                        return response()->download($path)->deleteFileAfterSend();
                    })
                    ->color('success'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAfipMapucheSicosses::route('/'),
            'create' => Pages\CreateAfipMapucheSicoss::route('/create'),
            'import' => Pages\ImportAfipMapucheSicoss::route('/import'),
        ];
    }


    public static function generarArchivoSicoss(Collection $registros = null): string
    {
        $registros = $registros ?? static::getModel()::all();
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
            $linea .= $formatearDecimal($registro->rem_dec_788_05, 12);
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
            $linea .= $formatearDecimal($registro->ley_27430, 12);
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
