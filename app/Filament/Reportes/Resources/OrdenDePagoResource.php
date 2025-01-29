<?php

namespace App\Filament\Reportes\Resources;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Reportes\RepOrdenPagoModel;
use App\Filament\Reportes\Resources\OrdenDePagoResource\Pages;
use App\Filament\Reportes\Resources\OrdenDePagoResource\Pages\ListReportes;

class OrdenDePagoResource extends Resource
{
    protected static ?string $model = RepOrdenPagoModel::class;
    protected static ?string $modelLabel = 'Orden de Pago';
    protected static ?string $navigationLabel = 'Orden de Pago';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Reportes';

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
                TextColumn::make('remunerativo')->money('ARS')->label('Remunerativo')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('no_remunerativo')->money('ARS')->label('No Remunerativo')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('otros_no_remunerativo')->money('ARS')->label('Otros No Remunerativo')->toggleable()->toggledHiddenByDefault(),
                TextColumn::make('bruto')->money('ARS')->label('bruto'),
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
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('No hay reportes generados')
            ->emptyStateDescription('Para generar un reporte de órdenes de pago sigue estos pasos:
                1. Haz clic en "Generar Reporte"
                2. Selecciona las liquidaciones que deseas incluir
                3. Presiona "Generar OP" para crear el reporte')
            ->emptyStateActions([
                //
            ])
            ;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReportes::route('/'),
            'reporte' => Pages\ReporteOrdenPago::route('/crear'),
        ];
    }


    public function generarReporte(): bool
    {
        try {
            $selectedLiquidaciones = $this->getLiquidacionesSeleccionadas();
            DB::select('SELECT suc.rep_orden_pago(?)', ['{' . implode(',', $selectedLiquidaciones) . '}']);
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

    public static function getEloquentQuery(): Builder
    {
        // Devuelve una consulta Eloquent modificada para el modelo Reporte
        return parent::getEloquentQuery()
            // Carga anticipadamente la relación 'unidadAcademica' para evitar el problema N+1
            ->with('unidadAcademica')
            // Ordena los resultados por el campo 'nro_liqui' en orden ascendente
            ->orderBy('nro_liqui', 'asc');
    }



    /**
     * Actualiza las liquidaciones seleccionadas en la sesión.
     *
     * @param array $liquidaciones Las liquidaciones seleccionadas.
     * @return void
     */
    #[On('liquidaciones-seleccionadas')]
    public function actualizarLiquidacionesSeleccionadas(array $liquidaciones): void
    {
        // Almacena las liquidaciones seleccionadas en la sesión
        session(['idsLiquiSelected' => $liquidaciones]);
        // Registra información de depuración sobre las liquidaciones seleccionadas
        Log::debug("isdLiquiSelected", ['state' => $liquidaciones]);
    }
}
