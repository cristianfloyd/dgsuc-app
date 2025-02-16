<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use App\Models\Mapuche\Dh22;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use App\Services\WorkflowService;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Filters\SelectFilter;
use App\Models\AfipMapucheMiSimplificacion;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Pagination\LengthAwarePaginator;
use Filament\Tables\Concerns\InteractsWithTable;

class ParaMiSimplificacion extends Component implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    #[Url(history: true, as: 's')]
    public $search = '';
    #[Url(history: true)]
    public int $perPage = 5;
    public $isFinished = false;
    protected $workflowService;
    protected $processLog;
    protected $step;
    public $selectedLiquidacion = null;

    public function boot(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }
    public function updateSearch()
    {
        $this->resetPage();
    }
    public function mount()
    {
        //
    }

    #[Computed()]
    public function headers(): array
    {
        // Get the headers from the database or from the model AfipMapucheMiSimplificacion
        $instance = new AfipMapucheMiSimplificacion();
        $headers = $instance->getTableHeaders();
        return $headers;
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('selectedLiquidacion')
                ->label('Liquidación')
                ->options(function () {
                    return Dh22::query()->where('sino_cerra', 'S')
                        ->orderBy('nro_liqui', 'desc')
                        ->limit(12)
                        ->pluck('desc_liqui', 'nro_liqui');
                })
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(function ($state) {
                    if ($state) {
                        $this->dispatch('idLiquiSelected',
                            nro_liqui: $state
                        );
                    }
                })
                ->placeholder('Seleccione una liquidación')
                ->required()
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = AfipMapucheMiSimplificacion::query();

                if ($this->selectedLiquidacion) {
                    $query->where('nro_liqui', $this->selectedLiquidacion);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('nro_legaj')
                    ->label('Legajo')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('periodo_fiscal')
                    ->label('Período Fiscal')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('cuil')
                    ->label('CUIL')
                    ->searchable(),
                TextColumn::make('inicio_rel_lab')
                    ->label('Inicio Relación')
                    ->date()
                    ->sortable(),
                TextColumn::make('fin_rel_lab')
                    ->label('Fin Relación')
                    ->date()
                    ->sortable(),
                TextColumn::make('retribucion_pactada')
                    ->label('Retribución')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: ',',
                        thousandsSeparator: '.'
                    )
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('periodo_fiscal')
                    ->label('Período')
                    ->options(fn () => AfipMapucheMiSimplificacion::distinct()
                        ->pluck('periodo_fiscal', 'periodo_fiscal')
                        ->toArray())
                    ->multiple(),
            ])
            ->actions([
                Action::make('exportar')
                    ->label('Exportar TXT')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(fn () => $this->exportarTxt())
                    ->requiresConfirmation(),
            ])
            ->defaultSort('periodo_fiscal', 'desc')
            ->paginated([5, 10, 25, 50, 100])
            ->striped()
            ->searchable()
            // ->persistFilters()
            ->persistSearchInSession()
            ->persistSortInSession();
    }

    public function exportarTxt()
    {
        $fieldLengths = [
            'tipo_registro' => 2,
            'codigo_movimiento' => 2,
            'cuil' => 11,
            'trabajador_agropecuario' => 1,
            'modalidad_contrato' => 3,
            'inicio_rel_laboral' => 10,
            'fin_rel_laboral' => 10,
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

        $data = AfipMapucheMiSimplificacion::all(array_keys($fieldLengths))->toArray();

        $txtContent = "";
        foreach ($data as $row) {
            $line = "";
            foreach ($fieldLengths as $field => $length) {
                $value = $row[$field] ?? '';
                $value = $value === null ? '' : $value;
                $line .= str_pad($value, $length, '0', STR_PAD_LEFT);
            }
            $txtContent .= $line . "\n";
        }

        $fileName = 'exportacion_' . now()->format('Ymd_His') . '.txt';
        Storage::disk('local')->put($fileName, $txtContent);
        $filePath = storage_path("app/$fileName");
        $this->dispatch('download-mi-simplificacion', $filePath);
        return response()->download($filePath)->deleteFileAfterSend(true);
    }


    public function toggleFinished()
    {
        //
    }




    public function render()
    {
        return view('livewire.para-mi-simplificacion');
    }
}
