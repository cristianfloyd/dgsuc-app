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
    public function mount(){
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
        // Recopilar los datos de las columnas que deseas exportar
        $data = AfipMapucheMiSimplificacion::all([
            'tipo_registro',
            'codigo_movimiento',
            'cuil',
            'trabajador_agropecuario',
            'modalidad_contrato',
            'inicio_rel_laboral',
            'fin_rel_laboral',
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
            'tipo_servicio',
            'categoria',
            'fecha_susp_serv_temp',
            'nro_form_agro',
            'covid'
        ])->toArray();

        // Generar el contenido del archivo TXT
        $txtContent = "";
        foreach ($data as $row) {
            $txtContent .= implode("", $row) . "\n";
        }

        // Crear un nombre de archivo único
        $fileName = 'exportacion_' . now()->format('Ymd_His') . '.txt';

        // Guardar el archivo temporalmente
        Storage::disk('local')->put($fileName, $txtContent);

        // Generar la URL de descarga
        $filePath = storage_path("app/$fileName");

        // Descargar el archivo
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

        return view('livewire.para-mi-simplificacion',[
            'dataTable' => AfipMapucheMiSimplificacion::search($this->search)
                ->paginate($this->perPage)
                ,
        ]);
    }
}
