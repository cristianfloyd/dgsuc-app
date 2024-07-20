<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\AfipMapucheMiSimplificacion;
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
            log::info('createTable() Tabla creada exitosamente.');
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
        Log::info('dropTableTemp()');
        $this->dispatch('toggle-show-drop');
    }

    #[On('insert-table-temp')]
    public function insertTableTemp($nro_liqui, $periodo_fiscal, $cuils)
    {
        Log::info('insertTableTemp()');
        $this->cuils = $cuils;
        if(TableModel::insertTable($cuils) ){
            $this->dispatch('inserted-table-temp');
        } else{
            $this->dispatch('error-inserted-table-temp');
        }
    }

    #[On('mapuche-mi-simplificacion')]
    public function mapucheMiSimplificacion($nroLiqui, $periodoFiscal)
    {
        //Validad parametros de entrada
        if(empty($nroLiqui) || empty($periodoFiscal)){
            $this->dispatch('error-mapuche-mi-simplificacion', 'nroliqui o periodofiscal vacios');
            return;
        }

        $instance = new AfipMapucheMiSimplificacion();
        $table = 'suc.afip_mapuche_mi_simplificacion';
        $fresh = false;

        //Chequear si la tabla no existe
        if(!Schema::hasTable($table)){
            $this->dispatch('error-mapuche-mi-simplificacion', 'La tabla no existe');
            //Crear la tabla
            sleep(1);
            // de el modelo @app/Models/AfipMapucheMiSimplificacion.php invocar el metodo createTable()
            if(!$instance->createTable() ){
                $this->dispatch('error-mapuche-mi-simplificacion', 'La tabla no se creo');
                return;
            }
            $this->dispatch('success-mapuche-mi-simplificacion', 'La tabla se creo exitosamente');
            $fresh = true;
        }

        //Chequear si la tabla esta vacia
        $tableHasData = $instance->get();
        if($tableHasData && !$fresh){
            $this->dispatch('error-mapuche-mi-simplificacion', 'La tabla no esta vacia');
            sleep(1);
            $this->dispatch('success-mapuche-mi-simplificacion', 'La no tabla esta vacia. Intentando vaciar');
            // Trucar y resetear identidades.
            AfipMapucheMiSimplificacion::truncate();
        }


        Log::info('mapucheMiSimplificacion() ');
        $result = TableModel::mapucheMiSimplificacion(
            $nroLiqui,
            $periodoFiscal
            );
        if($result){
            $this->dispatch('success-mapuche-mi-simplificacion');
        } else{
            $this->dispatch('error-mapuche-mi-simplificacion');
        }
    }


    public function boot()
    {
        Log::info('TablaTempCuils boot');
    }
    public function mount()
    {
        log::info('TablaTempCuils mout');
    }

    public function render()
    {
        Log::info('render TablaTempCuils');
        return view('livewire.tabla-temp-cuils');
    }
}
