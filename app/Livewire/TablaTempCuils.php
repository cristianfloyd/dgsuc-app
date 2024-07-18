<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\TablaTempCuils as TableModel;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class TablaTempCuils extends Component
{
    public $cuils = [];
    public $selectedCuil = null;

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
            session()->flash('success', 'Tabla creada exitosamente.');
            log::info('Tabla creada exitosamente.');
            $this->dispatch('table-created');
            // return true;
        } else {
            session()->flash('error', 'La tabla ya existe.');
            Log::info('La tabla ya existe.');
            $this->dispatch('table-exists');
            // return false;
        }
    }

    public function boot()
    {
        Log::info('Cargando el componente TablaTempCuils');
    }
    public function mount()
    {
        log::info('Montando el componente TablaTempCuils');
    }
    public function render()
    {
        return view('livewire.tabla-temp-cuils');
    }
}
