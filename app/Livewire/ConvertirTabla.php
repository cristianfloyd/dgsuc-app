<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UploadedFile;
use App\Models\AfipSicossDesdeMapuche;
use function PHPUnit\Framework\isEmpty;

use Illuminate\Support\Facades\Storage;
use App\Models\AfipImportacionCrudaModel;

class ConvertirTabla extends Component
{
    public $test;
    public $tabla;
    public $listadoArchivos;
    public $selectedArchivo;
    public $archivoName;
    public $archivo;
    public $columnWidths = [
            // Anchos de columna para cada campo
            6, 11, 30, 1, 2, 2, 2, 3, 2, 5, 3, 6, 2, 12, 12, 9, 9, 9, 9, 9, 50, 12, 12, 12, 2, 1, 9, 1, 9, 1, 2, 2, 2, 2, 2, 2, 12, 12, 12, 12, 12, 9, 12, 1, 12, 1, 12, 12, 12, 12, 3, 12, 12, 9, 12, 9, 3, 1, 12, 12, 12
            ];
    public $cantRegistros;
    public $lineCount;
    public $filepath;
    public $periodoFiscal;
    public $filasExtraidas;

    public function importarTabla()
    {
        $AfipimportacionCruda = new afipImportacionCrudaModel();
        // en $tabla almacenar un select de toda la tabla del modelo afipimportacioncrudamodel
        $this->tabla = $AfipimportacionCruda->all();
        // contar la cantidad de lineas que hay en la tabla $this->tabla si no esta vacia
        if (!empty($this->tabla)) {
            $this->lineCount = count($this->tabla);
        } else {
            // tabla vacia, lanzar una excepción o manejar el error de otra forma
            return null;
        }

        $this->filasExtraidas = $this->extraerFilas();

        $afipSicoss = new AfipSicossDesdeMapuche();
        $tabla = $afipSicoss->procesarTabla($this->filasExtraidas, $this->periodoFiscal);
        $resultado = $afipSicoss::insertarDatosMasivos($tabla);
        if ($resultado) {
            dump( "Datos insertados correctamente");
        }else {
            dump("Algo malo paso :( ");
        }
    }

    public function contarLineas(): void
    {
        $archivoRuta = Storage::path("/public/{$this->filepath}");
        $AfipimportacionCruda = new AfipSicossDesdeMapuche();
        $resultado = $AfipimportacionCruda->contarCaracteresPorLinea($archivoRuta);

    }
    public function procesarLinea($linea)
    {

        //$this->procesarLinea($linea);
    }

    public function cantRegistros( )
    {
        // calcular la cantidad de registros en el array $this->columnWidths
        $this->cantRegistros = count($this->columnWidths);

    }
    public function extraerFilas():array
    {
        $filasExtraidas = [];

        foreach ($this->tabla as $fila) {
            $filasExtraidas[] = $fila->linea_completa;
        }
        return $filasExtraidas;
    }

    public function mount()
    {
        $this->listadoArchivos = UploadedFile::all();
        $this->periodoFiscal = '';
    }

    public function seleccionarArchivo(): bool
    {
        // Buscar el archivo por ID
        $archivo = UploadedFile::find($this->selectedArchivo);
        // dump($archivo);
        // Verificar si el archivo existe
        if ($archivo) {
            $this->archivoName = $archivo->original_name;
            $this->periodoFiscal = $archivo->periodo_fiscal;
            $this->archivo = $archivo;
            $this->filepath = $archivo->file_path;
            return true;
        } else {
            return false; // O puedes lanzar una excepción o manejar el error de otra forma
        }
    }

    // Si $selectedArchivo se actualiza, se ejecuta este método seleccioarArchivo()
    public function updatedSelectedArchivo()
    {
        try {
            $this->seleccionarArchivo();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.convertir-tabla');
    }
}
