<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;
use App\Services\WorkflowService;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Storage;
use App\Models\AfipMapucheMiSimplificacion;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Pagination\LengthAwarePaginator;
use Filament\Tables\Concerns\InteractsWithTable;

class ParaMiSimplificacion extends Component
{
    use WithPagination;

    #[Url(history: true, as: 's')]
    public $search = '';
    #[Url(history: true)]
    public int $perPage = 5;
    public $isFinished = false;
    protected $workflowService;
    protected $processLog;
    protected $step;


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

    public function exportarTxt()
    {
        $fieldLengths = [
            'tipo_registro' => 1,
            'codigo_movimiento' => 1,
            'cuil' => 11,
            'trabajador_agropecuario' => 1,
            'modalidad_contrato' => 2,
            'inicio_rel_laboral' => 8,
            'fin_rel_laboral' => 8,
            'obra_social' => 6,
            'codigo_situacion_baja' => 2,
            'fecha_tel_renuncia' => 8,
            'retribucion_pactada' => 12,
            'modalidad_liquidacion' => 1,
            'domicilio' => 50,
            'actividad' => 3,
            'puesto' => 3,
            'rectificacion' => 1,
            'ccct' => 18,
            'tipo_servicio' => 1,
            'categoria' => 3,
            'fecha_susp_serv_temp' => 8,
            'nro_form_agro' => 12,
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

        return response()->download($filePath)->deleteFileAfterSend(true);
    }


    public function toggleFinished()
    {
        //
    }




    public function render()
    {
        $instance = new AfipMapucheMiSimplificacion();
        $dataTable = $instance->search($this->search)->take(10)->paginate(5);

        return view('livewire.para-mi-simplificacion', [
            'dataTable' => AfipMapucheMiSimplificacion::search($this->search)
                ->paginate($this->perPage),
        ]);
    }
}
