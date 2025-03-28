<?php

namespace App\Filament\Afip\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\ValueObjects\NroLiqui;
use Filament\Resources\Resource;
use App\Models\ControlDiferencias;
use Illuminate\Support\HtmlString;
use App\Services\CombinacionesService;
use App\Models\ControlAportesDiferencia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Afip\Resources\ControlDiferenciasResource\Pages;
use App\Filament\Afip\Resources\ControlDiferenciasResource\RelationManagers;

class ControlDiferenciasResource extends Resource
{
    protected static ?string $model = ControlAportesDiferencia::class;
    protected static ?string $title = 'Control de Diferencias';
    protected static ?string $navigationGroup = 'SICOSS';
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
                Tables\Columns\TextColumn::make('dh01.nro_legaj')
                    ->label('Legajo')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cuil')
                    ->label('Nombre')
                    ->searchable(),

                Tables\Columns\TextColumn::make('diferencia')
                    ->label('Diferencia')
                    ->money('ARS')
                    ->sortable()
                    ->alignRight(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('buscarCombinaciones')
                    ->label('Buscar Combinaciones')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('primary')
                    ->modalWidth('6xl')
                    ->modalHeading(fn($record) => "Combinaciones para Legajo {$record->dh01->nro_legaj}")
                    ->modalSubheading(fn($record) => "Buscando combinaciones que sumen aproximadamente $ " . number_format($record->diferencia, 2, ',', '.'))
                    ->modalContent(function ($record) {
                        // Obtener el número de liquidación (asumiendo que está en el modelo o en sesión)
                        $nroLiqui = new NroLiqui(session('nro_liqui', 10)); // Ajusta según tu implementación

                        // Buscar combinaciones
                        $combinacionesService = app(CombinacionesService::class);
                        $resultado = $combinacionesService->buscarCombinaciones(
                            $record->dh01->nro_legaj,
                            $nroLiqui,
                            $record->diferencia,
                            0.01 // Tolerancia
                        );

                        // Preparar el HTML para mostrar los resultados
                        $html = '<div class="p-4">';

                        if (!$resultado['success']) {
                            $html .= '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">';
                            $html .= $resultado['message'];
                            $html .= '</div>';
                            $html .= '</div>';
                            return new HtmlString($html);
                        }

                        if (empty($resultado['combinaciones'])) {
                            $html .= '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">';
                            $html .= 'No se encontraron combinaciones que se aproximen al valor objetivo.';
                            $html .= '</div>';
                            $html .= '</div>';
                            return new HtmlString($html);
                        }

                        // Mostrar las combinaciones encontradas
                        $html .= '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">';
                        $html .= "Se encontraron " . count($resultado['combinaciones']) . " posibles combinaciones.";
                        $html .= '</div>';

                        $html .= '<div class="overflow-x-auto">';
                        $html .= '<table class="min-w-full divide-y divide-gray-200">';
                        $html .= '<thead class="bg-gray-50">';
                        $html .= '<tr>';
                        $html .= '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>';
                        $html .= '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conceptos</th>';
                        $html .= '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>';
                        $html .= '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diferencia</th>';
                        $html .= '<th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>';
                        $html .= '</tr>';
                        $html .= '</thead>';
                        $html .= '<tbody class="bg-white divide-y divide-gray-200">';

                        foreach ($resultado['combinaciones'] as $index => $combinacion) {
                            $html .= '<tr' . ($index % 2 ? ' class="bg-gray-50"' : '') . '>';
                            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' . ($index + 1) . '</td>';

                            // Conceptos
                            $html .= '<td class="px-6 py-4 text-sm text-gray-500">';
                            $html .= '<ul class="list-disc pl-5">';
                            foreach ($combinacion['items'] as $item) {
                                $html .= '<li>';
                                $html .= 'Concepto: ' . $item['codn_conce'] . ', ';
                                $html .= 'Importe: $' . number_format($item['impp_conce'], 2, ',', '.') . ', ';
                                $html .= 'Tipo: ' . $item['tipo_conce'];
                                $html .= '</li>';
                            }
                            $html .= '</ul>';
                            $html .= '</td>';

                            // Total
                            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">';
                            $html .= '$' . number_format($combinacion['total'], 2, ',', '.');
                            $html .= '</td>';

                            // Diferencia
                            $diferencia = abs($combinacion['total'] - $record->diferencia);
                            $colorClass = $diferencia < 0.01 ? 'text-green-600 font-bold' : 'text-amber-600';
                            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm ' . $colorClass . '">';
                            $html .= '$' . number_format($diferencia, 2, ',', '.');
                            $html .= '</td>';

                            // Acciones
                            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">';
                            $html .= '<button type="button" class="text-indigo-600 hover:text-indigo-900" onclick="aplicarCombinacion(' . htmlspecialchars(json_encode($combinacion)) . ', ' . $record->id . ')">Aplicar</button>';
                            $html .= '</td>';

                            $html .= '</tr>';
                        }

                        $html .= '</tbody>';
                        $html .= '</table>';
                        $html .= '</div>';

                        // Script para manejar la acción de aplicar combinación
                        $html .= '<script>
                            function aplicarCombinacion(combinacion, recordId) {
                                if (confirm("¿Está seguro de aplicar esta combinación?")) {
                                    // Aquí puedes implementar la lógica para aplicar la combinación
                                    // Por ejemplo, enviar una petición AJAX o usar Livewire
                                    console.log("Aplicando combinación:", combinacion, "para el registro:", recordId);

                                    // Ejemplo de llamada a un endpoint
                                    fetch("/api/aplicar-combinacion", {
                                        method: "POST",
                                        headers: {
                                            "Content-Type": "application/json",
                                            "X-CSRF-TOKEN": document.querySelector("meta[name=\'csrf-token\']").content
                                        },
                                        body: JSON.stringify({
                                            combinacion: combinacion,
                                            recordId: recordId
                                        })
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            alert("Combinación aplicada correctamente");
                                            // Cerrar el modal o recargar la página
                                            window.location.reload();
                                        } else {
                                            alert("Error al aplicar la combinación: " + data.message);
                                        }
                                    })
                                    .catch(error => {
                                        console.error("Error:", error);
                                        alert("Error al procesar la solicitud");
                                    });
                                }
                            }
                        </script>';

                        $html .= '</div>';

                        return new HtmlString($html);
                    })
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
            'index' => Pages\ListControlDiferencias::route('/'),
            'create' => Pages\CreateControlDiferencias::route('/create'),
            'edit' => Pages\EditControlDiferencias::route('/{record}/edit'),
        ];
    }
}
