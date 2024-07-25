<?php

namespace App\Livewire;

use App\FileProcessor;
use Livewire\Component;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\Log;
use App\Models\AfipRelacionesActivas as ModelsAfipRelacionesActivas;

class AfipRelacionesActivas extends Component
{
    public $showCard = true;
    public $relacionesActivas;
    public $archivosCargados;
    public $archivoSeleccionado; //este es el archivo que se va a abrir en la vista
    public $archivoSeleccionadoId; //este es el id del archivo que se va a abrir en la vista
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
    public $lineasProcesadas; //este es el array que va a devolver la funcion procesarLinea
    public $periodo_fiscal; //este es el periodo fiscal que se va a cargar en la tabla relaciones_activas
    private $fileProcessor;

    public function boot(FileProcessor $fileProcessor)
    {
        $this->fileProcessor = $fileProcessor;
    }

    public function mount()
    {
        $this->archivosCargados = UploadedFile::all();
    }
    public function render()
    {
        return view('livewire.afip-relaciones-activas');
    }


    public function updatedarchivoSeleccionadoId($value)
    {
        $this->archivoSeleccionado = $this->archivosCargados->find($value);
    }


    public function importar()
    {
        $this->validateSelectedFile();
        $lineasProcesadas = $this->fileProcessor->processFile($this->archivoSeleccionado, $this->columnWidths);
        $this->almacenarLineas($lineasProcesadas);
        // despachar un evento para actualizar la tabla
        $this->dispatch('datos-importados');
    }


    private function almacenarLineas($lineasProcesadas)
    {
        $afipRelacionesActivas = new ModelsAfipRelacionesActivas();
        $datosMapeados = collect($lineasProcesadas)
            ->map(function ($linea) use ($afipRelacionesActivas) {
                return $afipRelacionesActivas::mapearDatosAlModelo($linea);
            })->all();

        $resultado = $afipRelacionesActivas::insertarDatosMasivos($datosMapeados);
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
    private function validateSelectedFile(): void
    {
        if (!$this->archivoSeleccionado) {
            throw new \InvalidArgumentException('No se ha seleccionado ningún archivo para importar.');
        }

        if (!$this->archivoSeleccionado instanceof UploadedFile) {
            throw new \InvalidArgumentException('El archivo seleccionado no es válido.');
        }

        $filePath = storage_path('app/public/' . $this->archivoSeleccionado->file_path);
        if (!file_exists($filePath)) {
            throw new \RuntimeException('El archivo seleccionado no existe en el sistema.');
        }

        if (!is_readable($filePath)) {
            throw new \RuntimeException('No se puede leer el archivo seleccionado.');
        }

        // Validación adicional específica para el tipo de archivo esperado
        // Por ejemplo, verificar la extensión del archivo o su contenido
        $allowedExtensions = ['txt', 'csv'];
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new \InvalidArgumentException('El tipo de archivo seleccionado no es válido. Se esperaba un archivo .txt o .csv.');
        }

        // Si todas las validaciones pasan, el método no lanzará excepciones
    }



    //Aqui vamos a procesar el abrir el archivo seleccionado en la vista que va a estar en $archivoSeleccionado
    // public function importar()
    // {
    //     if (!$this->archivoSeleccionado) {
    //         // mostrar un mensaje que no se selecciono archivo
    //         Log::warning('No se selecciono archivo');
    //     }
    //     $this->periodo_fiscal = $this->archivoSeleccionado->periodo_fiscal;

    //     //aqui vamos a procesar el archivo seleccionado
    //     //primero vamos a abrir el archivo
    //     $file_path = storage_path() . '/app/public/' . $this->archivoSeleccionado->file_path;
    //     // dd($file_path);
    //     $file = fopen($file_path, "r"); //abrimos el archivo en modo lectura
    //     // dd($file);
    //     if ($file) {
    //         $i = 0; //este es el contador de lineas
    //         while (($line = fgets($file)) !== false) {
    //             // calcular el ancho de la linea
    //             $anchoLinea = strlen($line) - 1 + 6; //actualmente tiene 119 caracteres
    //             // dd($anchoLinea);

    //             // calcular el ancho de columnWidths
    //             $sumaAnchoColumnas = array_sum($this->columnWidths) + 1; // ser resta la ultima posicion porque no corresponde a una columna valida
    //             // dd($sumaAnchoColumnas);

    //             // Validar que el ancho de la línea coincida con la suma de los anchos de columna
    //             if ($anchoLinea !== $sumaAnchoColumnas) {
    //                 throw new \InvalidArgumentException('El ancho de la línea no coincide con la suma de los anchos de columna.');
    //             }

    //             // llamar a una funcion procesarlinea(array $columnWidths) y que retorne un array separando la linea
    //             //en columnas del ancho dado por el array columnWidths
    //             $lineaProcesada = $this->procesarLineaEspacios($line, $this->columnWidths); //esta funcion va a devolver un array con los campos de la linea
    //             // dd($lineaProcesada);
    //             $this->lineasProcesadas[$i] = $lineaProcesada;

    //             $i++; //aumentamos el contador de lineas

    //         }
    //         // dd($this->lineasProcesadas);
    //     } else {
    //         // Manejar error al abrir el archivo
    //         dd('Error al abrir el archivo.');
    //     }
    //     fclose($file);
    //     // llamar a una funcion para almacenar en la tabla
    //     // relaciones_activas
    //     $this->almacenar();
    // }



}
