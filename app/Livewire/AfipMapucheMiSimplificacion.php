<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\AfipMapucheMiSimplificacion as ModelsAfipMapucheMiSimplificacion;

class AfipMapucheMiSimplificacion extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public function table(Table $table): Table
    {
        return $table
            ->query(ModelsAfipMapucheMiSimplificacion::query())
            ->columns([
                TextColumn::make('nro_legaj')
                    ->label('Legajo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nro_liqui')
                    ->label('Nro. Liquidación')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sino_cerra')
                    ->label('Estado')
                    ->sortable(),
                TextColumn::make('desc_estado_liquidacion')
                    ->label('Desc. Estado')
                    ->sortable(),
                TextColumn::make('nro_cargo')
                    ->label('Cargo')
                    ->sortable(),
                TextColumn::make('periodo_fiscal')
                    ->label('Período')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('cuil')
                    ->label('CUIL')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trabajador_agropecuario')
                    ->label('Trab. Agrop.')
                    ->sortable(),
                TextColumn::make('modalidad_contrato')
                    ->label('Modalidad')
                    ->sortable(),
                TextColumn::make('inicio_rel_lab')
                    ->label('Inicio Rel.')
                    ->date()
                    ->sortable(),
                TextColumn::make('fin_rel_lab')
                    ->label('Fin Rel.')
                    ->date()
                    ->sortable(),
                TextColumn::make('obra_social')
                    ->label('Obra Social')
                    ->sortable(),
                TextColumn::make('retribucion_pactada')
                    ->label('Retribución')
                    ->sortable(),
                TextColumn::make('domicilio')
                    ->label('Domicilio')
                    ->searchable(),
                TextColumn::make('actividad')
                    ->label('Actividad')
                    ->sortable(),
                TextColumn::make('puesto')
                    ->label('Puesto')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('sino_cerra')
                    ->label('Estado')
                    ->options([
                        'A' => 'Abierta',
                        'C' => 'Cerrada',
                    ]),
                SelectFilter::make('periodo_fiscal')
                    ->label('Período Fiscal')
                    ->options(function () {
                        return ModelsAfipMapucheMiSimplificacion::distinct()
                            ->orderBy('periodo_fiscal', 'desc')
                            ->pluck('periodo_fiscal', 'periodo_fiscal');
                    }),
            ])
            ->headerActions([
                Action::make('exportTxt')
                    ->label('Exportar TXT')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn () => $this->exportarTxt())
                    ->color('success')
            ])
            ->defaultSort('nro_legaj', 'asc')
            ->paginated([5, 10, 25, 50])
            ->striped();
    }

    public function exportarTxt()
    {
        // Define field lengths according to the format
        $fieldLengths = [
            'tipo_registro' => 2,
            'codigo_movimiento' => 2,
            'cuil' => 11,
            'trabajador_agropecuario' => 1,
            'modalidad_contrato' => 3,
            'inicio_rel_lab' => 10,
            'fin_rel_lab' => 10,
            'obra_social' => 6,
            'codigo_situacion_baja' => 2,
            'fecha_tel_renuncia' => 10,
            'retribucion_pactada' => 15,
            'modalidad_liquidacion' => 1,
            'domicilio' => 5,
            'actividad' => 6,
            'puesto' => 4,
            'rectificacion' => 2,
            'ccct' => 10,
            'tipo_servicio' => 3,
            'categoria' => 6,
            'fecha_susp_serv_temp' => 10,
            'nro_form_agro' => 10,
            'covid' => 1
        ];

        // Get all records with the specified fields
        $data = ModelsAfipMapucheMiSimplificacion::all(array_keys($fieldLengths))->toArray();

        // Build the TXT content
        $txtContent = "";
        foreach ($data as $row) {
            $line = "";
            foreach ($fieldLengths as $field => $length) {
                $value = $row[$field] ?? '';
                // Convert null to empty string and ensure proper padding
                $value = $value === null ? '' : $value;
                $line .= str_pad($value, $length, '0', STR_PAD_LEFT);
            }
            $txtContent .= $line . "\n";
        }

        // Generate filename with timestamp
        $fileName = 'mi_simplificacion_' . now()->format('Ymd_His') . '.txt';

        // Store the file
        Storage::disk('local')->put($fileName, $txtContent);

        // Return download response
        return response()->download(storage_path("app/{$fileName}"))->deleteFileAfterSend(true);
    }

    public function render()
    {
        return view('livewire.afip-mapuche-mi-simplificacion');
    }
}
