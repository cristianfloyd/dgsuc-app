<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\UploadedFile;
use App\Models\AfipMapucheSicoss;
use App\Models\AfipSicossDesdeMapuche;
use App\Models\AfipImportacionCrudaModel;
use Illuminate\Support\Facades\Storage;

class MapucheSicoss extends Component
{
    public $tablasVacias = false;
    public $selectedArchivo;
    public $listadoArchivos;
    public $selectedArchivoID;
    public $filename;

    protected $filepath;
    protected $absolutePath;
    protected $periodoFiscal;
    protected $afipMapucheSicoss;
    protected $afipMapucheSicossTable;
    protected $afipImportacionCrudaTable;


    public function importarArchivo($archivoId = null)
    {
        if ($archivoId === null) {
            $archivoId = $this->selectedArchivoID;
        }

        $archivo = UploadedFile::findOrFail($archivoId);
        // dd($archivo);
        if($archivo){
            session()->flash('success','Archivo Encontrado');
        }
        $resultado = AfipSicossDesdeMapuche::importarDesdeArchivo($archivo->file_path, $archivo->periodo_fiscal);

        if ($resultado) {
            session()->flash('success', 'Importación completada con éxito.');
        } else {
            session()->flash('error', 'Hubo un problema durante la importación.');
        }
    }

    public function seleccionarArchivo()
    {

    }

    public function impotarTabla()
    {
        $this->verificarTablas();
        if($this->tablasVacias){
            $this->afipMapucheSicoss->importarTabla();
        }
    }


    /**
     * Verifica las tablas necesarias para el funcionamiento del componente.
     */
    public function verificarTablas(){
        $this->verifyAfipImportCrudoTable();
        $this->verifyAfipMapucheSicossTable();
        if($this->afipImportacionCrudaTable && !$this->afipMapucheSicossTable){
            $this->afipMapucheSicoss = new AfipSicossDesdeMapuche();
            $this->tablasVacias = false;
        }
    }

    /**
     * Verifica si la tabla de importación cruda de AFIP está vacía.
     */
    public function verifyAfipImportCrudoTable(){
        $this->afipImportacionCrudaTable = $this->verifyTableIsEmpty(AfipImportacionCrudaModel::class, 'AfipImportCrudo');
    }

    /**
     * Verifica si la tabla de Mapuche Sicoss de AFIP está vacía.
     */
    public function verifyAfipMapucheSicossTable(){
        $this->afipMapucheSicossTable = $this->verifyTableIsEmpty(AfipMapucheSicoss::class, 'AfipMapucheSicoss');
    }



    public function verifyTableIsEmpty($model, $tableName): bool
    {
        $modelInstance = new $model;

        $tableIsEmpty = $modelInstance->all()->isEmpty();

        if ($tableIsEmpty) {
            // Tabla vacia emite un mensaje de error
            session()->flash('tableIsEmpty', "La tabla $tableName está vacía.");
            return false;
        } else {
            // la tabla no esta vacia
            session()->flash('tableIsNotEmpty', "La tabla $tableName no está vacía.");
            return true;
        }
    }




    public function mount()
    {
        //$this->verifyAfipImportCrudoTable();
        //$this->verifyAfipMapucheSicossTable();
        //llamar al modelo UploadedFile para obtener todos los archivos subidos
        $this->listadoArchivos = UploadedFile::all();
    }

    public function updatedSelectedArchivoID($archivoId)
    {
        $this->selectedArchivo = UploadedFile::findOrFail($archivoId);
        $this->filepath = $this->selectedArchivo->file_path;
        $this->absolutePath =  storage::path($this->filepath);
        $this->periodoFiscal = $this->selectedArchivo->periodo_fiscal;
        $this->filename = $this->selectedArchivo->original_name;
    }

    public function render()
    {
        return view('livewire.mapuche-sicoss');
    }
}
