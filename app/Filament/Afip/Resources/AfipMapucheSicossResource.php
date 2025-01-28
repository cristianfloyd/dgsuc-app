<?php

namespace App\Filament\Afip\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Models\AfipMapucheSicoss;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\FilamentTableInitializationTrait;
use App\Traits\FilamentAfipMapucheSicossTableTrait;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Afip\Resources\AfipMapucheSicossResource\Pages;
use App\Contracts\TableService\AfipMapucheSicossTableServiceInterface;
use App\Filament\Afip\Resources\AfipMapucheSicossResource\RelationManagers;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Storage;

class AfipMapucheSicossResource extends Resource
{
    use FilamentAfipMapucheSicossTableTrait;

    protected static ?string $model = AfipMapucheSicoss::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'AFIP';




    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('periodo_fiscal')
                    ->label('Período Fiscal')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('cuil')
                    ->label('CUIL')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('apnom')
                    ->label('Apellido y Nombre')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('conyuge')
                    ->label('Cónyuge')
                    ->boolean(),
                Tables\Columns\TextColumn::make('cant_hijos')
                    ->label('Cant. Hijos')
                    ->numeric(),
                Tables\Columns\TextColumn::make('cod_situacion')
                    ->label('Situación'),
                Tables\Columns\TextColumn::make('cod_cond')
                    ->label('Condición'),
                Tables\Columns\TextColumn::make('rem_total')
                    ->label('Remuneración Total')
                    ->money('ARS'),
                Tables\Columns\TextColumn::make('rem_impo1')
                    ->label('Remuneración 1')
                    ->money('ARS'),
                Tables\Columns\TextColumn::make('asig_fam_pag')
                    ->label('Asig. Familiar')
                    ->money('ARS')

            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
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

    public static function generarArchivoSicoss(): string
    {
        $registros = static::getModel()::all();
        $contenido = '';

        // Función auxiliar para formatear decimales con 2 decimales
        $formatearDecimal = function($valor, $longitud) {
            $valor = $valor ?? 0;
            // Formatea el número con 2 decimales y punto como separador
            $numeroFormateado = number_format($valor, 2, '.', '');
            // Rellena con ceros a la izquierda hasta alcanzar la longitud deseada
            return str_pad($numeroFormateado, $longitud, '0', STR_PAD_LEFT);
        };

        foreach ($registros as $registro) {
            $linea = '';

            // Datos de identificación personal
            $linea .= str_pad($registro->cuil ?? '0', 11, '0', STR_PAD_LEFT);
            $linea .= str_pad(substr($registro->apnom ?? '', 0, 30), 30, ' ', STR_PAD_RIGHT);

            // Datos familiares
            $linea .= $registro->conyuge ? '1' : '0';
            $linea .= str_pad($registro->cant_hijos ?? '0', 2, '0', STR_PAD_LEFT);

            // Datos situación laboral
            $linea .= str_pad($registro->cod_situacion ?? '0', 2, '0', STR_PAD_LEFT);
            $linea .= str_pad($registro->cod_cond ?? '0', 2, '0', STR_PAD_LEFT);
            $linea .= str_pad($registro->cod_act ?? '0', 3, '0', STR_PAD_LEFT);
            $linea .= str_pad($registro->cod_zona ?? '0', 2, '0', STR_PAD_LEFT);

            // Datos aportes y obra social
            $linea .= ' ' . number_format($registro->porc_aporte ?? 0, 4, '.', '');
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
