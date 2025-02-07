<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Table;
use App\Enums\PuestoDesempenado;
use App\Services\ColumnMetadata;
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
                    ->action(fn() => $this->exportarTxt())
                    ->color('success')
            ])
            ->defaultSort('nro_legaj', 'asc')
            ->paginated([5, 10, 25, 50])
            ->striped();
    }

    public function exportarTxt()
    {
        // Usar los mismos anchos de columna que en ColumnMetadata
        $columnMetadata = new ColumnMetadata();
        $columnMetadata->setSystem('miSimplificacion');
        $columnWidths = $columnMetadata->getWidths();


        // Mapeo de campos de la base de datos a la posición en el archivo
        $fieldOrder = [
            'tipo_registro',
            'codigo_movimiento',
            'cuil',
            'trabajador_agropecuario',
            'modalidad_contrato',
            'inicio_rel_laboral',
            'fin_rel_lab',
            'obra_social',
            'codigo_situacion_baja',
            'fecha_tel_renuncia',
            'retribucion_pactada',
            'modalidad_liquidacion',
            'domicilio',
            'actividad',
            'puesto',
            'rectificacion',
            'ccct',
            'categoria',
            'tipo_servicio',
            'fecha_susp_serv_temp',
            'nro_form_agro',
            'covid'
        ];

        // Obtener los registros
        $records = ModelsAfipMapucheMiSimplificacion::all();

        // Construir el contenido del archivo
        $txtContent = "";
        foreach ($records as $record) {
            $line = "";
            foreach ($fieldOrder as $index => $field) {
                $width = $columnWidths[$index];

                // Manejar el caso especial de espacios en blanco al final
                if ($field === 'espacios_en_blanco') {
                    $value = str_repeat(' ', $width);
                } else {
                    $value = $record->{$field} ?? '';

                    // Formatear fechas si es necesario
                    if (in_array($field, ['inicio_rel_lab', 'fin_rel_lab'])) {
                        $value = $value ? date('Y-m-d', strtotime($value)) : str_repeat(' ', $width);
                    } elseif ($field === 'fecha_tel_renuncia') {
                        $value = '0';
                    }

                    // Formatear números si es necesario
                    if ($field === 'retribucion_pactada') {
                        $value = number_format((float)$value, 2, '', ''); // Quitamos el punto decimal
                    }

                    // Formatear el campo nro_form_agro
                    if ($field === 'nro_form_agro') {
                        $value = '9999999999';
                    }

                    // Formatear el campo ccct (Código de Convenio Colectivo de Trabajo)
                    if ($field === 'ccct') {
                        $value = '9999/99';
                    }

                    // Formatear el campo puesto
                    if ($field === 'puesto') {
                        // Aseguramos obtener el valor (código) del enum
                        $value = $record->puesto?->value ?? '';
                    }
                }

                // Aplicar el padding según el tipo de campo
                if ($field === 'cuil' || $field === 'retribucion_pactada') {
                    $line .= str_pad($value, $width, '0', STR_PAD_LEFT);
                } else {
                    $line .= str_pad($value, $width, ' ', STR_PAD_RIGHT);
                }
            }
            $txtContent .= $line . "\n";
        }

        // Generar el nombre del archivo
        $fileName = 'mi_simplificacion_' . now()->format('Ymd_His') . '.txt';

        // Almacenar y descargar el archivo
        Storage::disk('local')->put($fileName, $txtContent);
        return response()->download(storage_path("app/{$fileName}"))->deleteFileAfterSend(true);
    }

    public function render()
    {
        return view('livewire.afip-mapuche-mi-simplificacion');
    }
}
