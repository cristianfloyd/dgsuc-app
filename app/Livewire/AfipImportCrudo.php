<?php

namespace App\Livewire;

use App\Models\afipImportacionCrudaModel;
use App\Models\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class AfipImportCrudo extends Component
{
    use WithPagination;

    public $listadoArchivos = [];

    public $selectedArchivo;

    public $selectedtable;

    public $tablas = [
        'afip_importacion_cruda',
        'afip_mapuch_sicos_importacion_cruda',
    ];

    public $ocultartabla = 0 ;

    public $datosImportados ;

    public $showButton = 0;

    public function save(): void
    {
        $archivo = UploadedFile::query()->findOrFail($this->selectedArchivo);
        $filepath = storage_path() . '/app/public/' . $archivo->file_path;
        $fileContent = Storage::get($filepath);

        // Crear una instancia de Request y asignar el contenido del archivo
        $request = new Request();
        $request->replace([
            'file_path' => $filepath,
            'file_content' => $fileContent,
        ]);
        $lineas = $this->importar($filepath);

        $this->showButton = 1;
        session()->flash('status', 'Archivo subido exitosamente!.');

    }

    public function mostrartabla(): void
    {
        $model = new AfipImportacionCrudaModel();
        $query = $model->getQuery();
        $query->paginate(10);
        $this->datosImportados = $query->get();
        //dd($this->datosImportados);
        $this->ocultartabla = true;
    }

    /*
    * La funcion importar se encarga de importar un archivo de texto a la base de datos
    * utilizando el modelo AfipImportacionCrudaModel y los metodos de ese modelo
    * @return int numero de lineas
    */
    public function importar($filepath): int
    {
        $ImportacionCruda = new afipImportacionCrudaModel();
        $lineas = $ImportacionCruda->importarArchivo($filepath);
        return $lineas;
    }

    public function mount(): void
    {
        $this->listadoArchivos = UploadedFile::all();
    }

    public function render()
    {
        return view('livewire.afip-import-crudo');
    }
}
