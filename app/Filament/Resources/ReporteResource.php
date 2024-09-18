<?php

namespace App\Filament\Resources;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use App\Models\Reportes\RepOrdenPagoModel;
use App\Filament\Resources\ReporteResource\Pages;

class ReporteResource extends Resource
{
    protected static ?string $model = RepOrdenPagoModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nro_liqui')->label('Nro. Liquidación'),
                TextColumn::make('banco')->label('Banco'),
                TextColumn::make('codn_funci')->label('Función'),
                TextColumn::make('codn_fuent')->label('Fuente'),
                TextColumn::make('codc_uacad')->label('Unidad Académica'),
                TextColumn::make('caracter')->label('Carácter'),
                TextColumn::make('codn_progr')->label('Programa'),
                TextColumn::make('remunerativo')->money('ARS')->label('Remunerativo'),
                TextColumn::make('no_remunerativo')->money('ARS')->label('No Remunerativo'),
                TextColumn::make('descuentos')->money('ARS'),
                TextColumn::make('aportes')->money('ARS'),
                TextColumn::make('estipendio')->money('ARS'),
                TextColumn::make('med_resid')->money('ARS')->label('Med. Resid.'),
                TextColumn::make('productividad')->money('ARS'),
                TextColumn::make('sal_fam')->money('ARS')->label('Sal. Familiar'),
                TextColumn::make('hs_extras')->money('ARS')->label('Hs. Extras'),
                TextColumn::make('total')->money('ARS'),
            ])
            ->filters([
                //
            ])
            ->actions([

            ])
            ->bulkActions([
                //
            ])
            ;
    }



    public static function getPages(): array
    {
        return [
            //'index' => Pages\ListReportes::route('/'),
            //'create' => Pages\CreateReporte::route('/create'),
            //'edit' => Pages\EditReporte::route('/{record}/edit'),
            'index' => Pages\ListReportes::route('/'),
        ];
    }

    public static function getActions(): array
    {
        return [
            Action::make('ordenPagoReporte')
                ->label('Orden de Pago')
                ->modalContent(fn () => view('modals.orden-pago-reporte', ['liquidacionId' => 1]))
                ->modalWidth('7xl'),
            Action::make('generarReporte')
                ->label('Generar Reporte')
                //->action(fn() => $this->generarReporte())
                ->action(function () {
                    // Aquí se llamaría a un servicio que ejecute la función almacenada
                    if($this->generarReporte())
                    {
                        Notification::make()->title('Reporte generado')->success()->send();
                    }
                })

            // Aca agregar otros reportes
        ];
    }

    public function generarReporte(): bool
    {
        try {
            $selectedLiquidaciones = $this->getLiquidacionesSeleccionadas();
            DB::select('SELECT suc.rep_orden_pago(?)', ['{' . implode(',', $selectedLiquidaciones) . '}']);
            Notification::make()->title('Reporte generado')->success()->send();
            return true;
        } catch (\Exception $e) {
            Log::error('Error al generar el reporte: ' . $e->getMessage());
            Notification::make()->title('Error al generar el reporte')->danger()->send();
            return false;
        }
    }

    public function getLiquidacionesSeleccionadas()
    {
        $data = session('idsLiquiSelected', []);
        Log::debug("idsLiquiSelected", ['data' => $data]);
        return $data;
    }

    #[On('liquidaciones-seleccionadas')]
    public function actualizarLiquidacionesSeleccionadas($liquidaciones)
    {
        session(['idsLiquiSelected' => $liquidaciones]);
        Log::debug("isdLiquiSelected", ['state' => $liquidaciones]);
    }
}
