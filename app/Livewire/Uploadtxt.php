<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UploadedFile;
use App\Models\OrigenesModel;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;

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
        $filename = $originalName . '_' . $time . '.' . $extension;


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
        $this->validate([
            'archivotxt' => 'required|max:20480', // 15MB en kilobytes
        ]);
        $this->file_path = $this->archivotxt->store('afiptxt', 'public');
        $this->uploadfilemodel();
        session()->flash('message', 'Archivo subido exitosamente.');
        // Resetear la propiedad para limpiar el formulario
        $this->reset('archivotxt');
    }

    public function index()
    {
        $this->importaciones = UploadedFile::all();
    }

    public function mount()
    {
        $this->index();
        //obtener los origenes de la base de datos OrigenesModel
        $origenes = new OrigenesModel();
        $this->origenes = $origenes->get();
    }
    public function render()
    {
        return view('livewire.uploadtxt');
    }
}
