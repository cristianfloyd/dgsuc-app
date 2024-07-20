<?php

namespace App\Livewire;

use Exception;
use Livewire\Component;
use App\Models\UploadedFile;
use App\Models\OrigenesModel;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Validation\ValidatesRequests;


class Uploadtxt extends Component
{
    use WithFileUploads;
    #[Rule('max:20480')] // 20MB Max
    public $archivotxt;

    public $headers = [];
    public $importaciones;
    public $archivoModel = [];
    public $file_path;
    public $periodo_fiscal;
    public $origenes = ['afip', 'mapuche'];
    public $selectedOrigen;


    public function uploadfilemodel()
    {
        $file = $this->archivotxt;
        $originalName = $this->archivotxt->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $time = time();
        $file_path = $this->file_path;
        $filename = "{$originalName}_$time.$extension";


        $this->archivoModel = new UploadedFile();
        $this->archivoModel->filename = $filename;
        $this->archivoModel->original_name = $file->getClientOriginalName();
        $this->archivoModel->file_path = $file_path;
        $this->archivoModel->periodo_fiscal = $this->periodo_fiscal;

        $this->archivoModel->origen = OrigenesModel::find($this->selectedOrigen)->name;
        $this->archivoModel->user_id = auth()->user()->id;
        $this->archivoModel->user_name = auth()->user()->name;
        $this->archivoModel->save();
    }


    public function save()
    {

        $messages = [
            'archivotxt.required' => 'Por favor, seleccione un archivo para subir.',
            'archivotxt.mimes' => 'Solo se permiten archivos de tipo .txt.',
            'archivotxt.max' => 'El archivo no debe superar los 20MB.',
        ];

    try {
        $this->validate([
            'archivotxt' => 'required|file|mimes:txt|max:20480', // Asegurarse de que el archivo sea de tipo .txt
        ], $messages);


        $this->file_path = $this->archivotxt->store('/afiptxt', 'public');
        $this->uploadfilemodel();
        session()->flash('message', 'Archivo subido exitosamente.');
        // Resetear la propiedad para limpiar el formulario
        $this->reset('archivotxt');

    } catch (ValidationException $e) {
        //Handle validation errors
        $this->dispatch('validationError', $e->errors());

    } catch (Exception $e) {
        //Handle file upload or other unexpected errors
        $this->dispatch('fileUploadError', $e->getMessage());
    }
}



    public function mount()
    {
        $this->importaciones = UploadedFile::all();
        //obtener los origenes de la base de datos OrigenesModel
        $this->origenes = OrigenesModel::all();
    }
    public function render()
    {
        return view('livewire.uploadtxt');
    }
}
