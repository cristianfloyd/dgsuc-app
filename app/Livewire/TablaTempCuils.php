<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use App\Models\TablaTempCuils as TableModel;

class TablaTempCuils extends Component
{
    public $cuils = [];
    public $selectedCuil = null;
    public $cuilsNotInAfipLoaded;

    protected $tablaTempCuil;

    // metodo para crear una tabla temporal con el modelo TablaTempCuil
    #[On('crear-tabla-temp')]
    public function createTable():void
    {
        $this->tablaTempCuil = new TableModel();
        Log::info('Intentando crear la tabla temporal tablaTempCuil');
        // Lógica para crear la tabla temporal tablaTempCuil
        //primero verificar que la tabla no exista y si no existe  crearla y retornar un mensaje de exito
        if (!$this->tablaTempCuil->tableExists()) {
            $this->tablaTempCuil->createTable();
            // session()->flash('success', 'Tabla creada exitosamente.');
            log::info('Tabla creada exitosamente.');
            $this->dispatch('created');
            // return true;
        } else {
            // session()->flash('error', 'La tabla ya existe.');
            Log::info('La tabla ya existe.');
            Log::info('dispatch: tableexists');
            $this->dispatch('exists');
            // return false;
        }
    }
    #[On('drop-table-temp')]
    public function dropTableTemp()
    {
        TableModel::dropTable();
        Log::info('dropTableTemp');
        $this->dispatch('toggle-show-drop');
    }
    public function boot()
    {
        Log::info('boot TablaTempCuils');
    }
    public function mount()
    {
        log::info('mount TablaTempCuils');
    }

    public function render()
    {
        Log::info('render TablaTempCuils');
        return view('livewire.tabla-temp-cuils');
    }
}
