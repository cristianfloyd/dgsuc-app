<?php

namespace App\Livewire;

use App\Models\OrigenesModel;
use App\Models\UploadedFile;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class SubirArchivo extends Component
{
    use WithFileUploads;

    public $user_id;

    public $username;

    #[Validate('max:28480')]
    public $archivo;

    public $periodo_fiscal;

    public $origenes;

    public $selectedOrigen;

    public $importaciones;

    public function guardar(): void
    {
        $archivoCargado = new UploadedFile();

        $periodo_fiscal = $this->periodo_fiscal;

        $origen_id = $this->selectedOrigen ;
        $origen = $this->origenes->where('id', $origen_id)->first()->name;
        $file = $this->archivo;
        $original_name = $file->getClientOriginalName();
        $user_name = $this->username;
        $user_id = $this->user_id;
        $filename = pathinfo($original_name, \PATHINFO_FILENAME); //nombre del archivo
        $extension = pathinfo($original_name, \PATHINFO_EXTENSION); //extension del archivo
        $filename = $filename . '_' . time() . '.' . $extension; //nombre del archivo con marca de tiempo
        $filepath = $file->storeAs('public/archivos', $filename); //guardar el archivo en la carpeta publica

        $archivoCargado->periodo_fiscal = $periodo_fiscal;
        $archivoCargado->origen = $origen;
        $archivoCargado->filename = $filename; //nombre del archivo con marca de tiempo
        $archivoCargado->original_name = $original_name;
        $archivoCargado->file_path = $filepath; //guardar el archivo en la carpeta publica
        $archivoCargado->user_name = $user_name;
        $archivoCargado->user_id = $user_id;
        $archivoCargado->save(); //guardar en la base de datos
        // return redirect()->route('home');
    }

    public function mount(): void
    {
        $this->username = auth()->user()->name;
        $this->user_id = auth()->user()->id;
        //obtener los origenes de la base de datos OrigenesModel
        $origenes = new OrigenesModel();
        $this->origenes = $origenes->get();
        $this->importaciones = UploadedFile::all();
    }

    public function eliminar($id): void
    {
        $archivo = UploadedFile::findOrFail($id);
        $archivo->delete();
    }

    public function index(): void
    {
        $this->importaciones = UploadedFile::all();
    }

    public function render()
    {

        return view('livewire.subir-archivo');
    }
}
