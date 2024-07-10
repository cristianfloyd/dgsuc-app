<?php

namespace App\Livewire;

use App\Models\afipImportacionCrudaModel;
use App\Models\UploadedFile;
use Illuminate\Http\Request;
use Livewire\Component;

class ImportarArchivo extends Component
{
    public $modelo = 'afip_importacion_cruda';
    public $archivo;
    public $selectedArchivoId;
    public $lineasImportadas = 0;
    public $archivoModel;
    public $title, $content; //Variables de prueba
    public $importaciones;

    public function render()
    {
        $archivos = UploadedFile::all();
        return view('importar.index', compact('archivos'));
    }

    public function importaciones()
    {
        $archivos = UploadedFile::all();
        $this->importaciones = $archivos;
    }

    /**
     * Importa un archivo seleccionado por el usuario.
     *
     * Este método se encarga de procesar un archivo seleccionado por el usuario y realizar la importación de los datos contenidos en él. Primero, se muestra un mensaje de éxito en la sesión. Luego, se obtiene el modelo del archivo seleccionado y se procede a importar los datos utilizando un modelo de importación cruda de AFIP. Finalmente, se muestra un mensaje de éxito con la cantidad de líneas importadas.
     */
    public function importar()
    {
        session()->flash('status', 'Post successfully updated.');
        return $this->redirect('/dashboard');
    }

    public function show()
    {
        $this->mount();
        return view('importar.show');
    }

    public function mount()
    {
        $model = new afipImportacionCrudaModel();
        $this->lineasImportadas = $model->getDatosImportados();
    }
}
