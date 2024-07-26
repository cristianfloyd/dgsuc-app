<?php

namespace App\Livewire;

use App\Services\FileProcessorService;
use Livewire\Component;
use App\Models\UploadedFile;
use App\Services\WorkflowService;
use Illuminate\Support\Facades\Log;
use App\Models\AfipRelacionesActivas as ModelsAfipRelacionesActivas;
use App\services\DatabaseService;
use App\Services\ValidationService;

/**
 * Componente Livewire que maneja la importación de archivos AFIP y el almacenamiento de las relaciones activas.
 *
 * Este componente se encarga de:
 * - Mostrar la lista de archivos cargados y permitir la selección de uno de ellos.
 * - Procesar el archivo seleccionado, validar su contenido y almacenar las líneas procesadas en la base de datos.
 * - Manejar el flujo de trabajo de la importación, marcando los pasos correspondientes.
 * - Mostrar mensajes de éxito o error durante el proceso de importación.
 */
class AfipRelacionesActivas extends Component
{
    public $relacionesActivas;
    public $archivosCargados;
    public $archivoSeleccionado; //este es el archivo que se va a abrir en la vista
    public int $archivoSeleccionadoId; //este es el id del archivo que se va a abrir en la vista
    protected array $columnWidths = [
        6,  //periodo fiscal
        2,  //codigo movimiento
        2,  //Tipo de registro
        11,  //CUIL del empleado
        1,  //Marca de trabajador agropecuario
        3,  //Modalidad de contrato
        10,  //Fecha de inicio de la rel. Laboral
        10,  //Fecha de fin relacion laboral
        6,  //Código de obra social
        2,  //codigo situacion baja
        10,  //Fecha telegrama renuncia
        15,  //Retribución pactada
        1,  //Modalidad de liquidación
        5,  //Sucursal-Domicilio de desempeño
        6,  //Actividad en el domicilio de desempeño
        4,  //Puesto desempeñado
        1,  //Rectificación
        10,  //Numero Formulario Agropecuario
        3,  //Tipo de Servicio
        6,  //Categoría Profesional
        7,  //Código de Convenio Colectivo de Trabajo
        4,  //Sin valores, en blanco
    ];
    public array $lineasProcesadas; //este es el array que va a devolver la funcion procesarLinea
    public string $periodo_fiscal; //este es el periodo fiscal que se va a cargar en la tabla relaciones_activas

    private $fileProcessor;
    private $databaseService;
    protected $workflowService;
    private $validationService;


    public function boot(
        FileProcessorService $fileProcessor,
        DatabaseService $databaseService,
        WorkflowService $workflowService,
        ValidationService $validationService
        )
    {
        $this->fileProcessor = $fileProcessor;
        $this->databaseService = $databaseService;
        $this->workflowService = $workflowService;
        $this->validationService = $validationService;
    }

    public function mount()
    {
        $this->archivosCargados = UploadedFile::all();
    }

    public function updatedarchivoSeleccionadoId($value)
    {
        $this->archivoSeleccionado = $this->archivosCargados->find($value);
    }


    /**
     * Importa un archivo AFIP y almacena las líneas procesadas en la base de datos.
     *
     * Este método se encarga de validar el archivo seleccionado, procesar su contenido y almacenar los datos en la base de datos.
     * También actualiza el estado del flujo de trabajo y envía mensajes de éxito o error según corresponda.
     *
     * @return void
     */
    public function importar()
    {
        $processLog = $this->workflowService->getLatestWorkflow() ?? $this->workflowService->startWorkflow();
        $this->workflowService->updateStep($processLog, 'import_archivo_afip', 'in_progress');

        try {
            if (!$this->archivoSeleccionado) {
                dd($this->archivoSeleccionado);
                throw new \Exception('No se ha seleccionado ningún archivo.');
            }

            $this->validationService->validateSelectedFile($this->archivoSeleccionado);
            $lineasProcesadas = $this->fileProcessor->processFile($this->archivoSeleccionado, $this->columnWidths);

            // dd($lineasProcesadas);

            $this->almacenarLineas($lineasProcesadas);


            $this->workflowService->completeStep($processLog, 'import_archivo_afip');
            $this->dispatch('datos-importados');
            $this->dispatch('show-success-message', ['message' => 'Se importó correctamente']);

            $nextStep = $this->workflowService->getNextStep('import_archivo_afip');
            sleep(3);
            if ($nextStep) {
                return redirect()->to($this->workflowService->getStepUrl($nextStep));
            }
        } catch (\Exception $e) {
            Log::error('Error en la importación: ' . $e->getMessage());
            $this->dispatch('show-error-message', ['message' => 'No se importó correctamente: ' . $e->getMessage()]);
            $this->workflowService->updateStep($processLog, 'import_archivo_afip', 'failed');
        }
    }


    private function almacenarLineas($lineasProcesadas)
    {
        $datosMapeados = collect($lineasProcesadas)
            ->map(function ($linea) {
                return $this->databaseService->mapearDatosAlModelo($linea);
            })->all();
        // dd($datosMapeados);

        $resultado = $this->databaseService->insertarDatosMasivos2($datosMapeados);
        $this->handleResultado($resultado);
    }

    private function handleResultado($resultado)
    {
        if ($resultado) {
            // mostrar un mensaje que se importo correctamente
            Log::info('Se importo correctamente');
            $this->dispatch('show-success-message', ['message' => 'Se importo correctamente']);
        } else {
            // mostrar un mensaje que no se importo correctamente
            Log::error('No se importo correctamente');
            $this->dispatch('show-error-message', ['message' => 'No se importo correctamente']);
        }
    }


    public function render()
    {
        return view('livewire.afip-relaciones-activas');
    }
}
