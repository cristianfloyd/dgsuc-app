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
    if (!$this->validarParametros($nroLiqui, $periodoFiscal)) {
        return;
    }

    $instance = new AfipMapucheMiSimplificacion();
    $table = 'suc.afip_mapuche_mi_simplificacion';

    if (!$this->verificarYCrearTabla($instance, $table)) {
        return;
    }

    $this->verificarYVaciarTabla($instance);

    $this->insertarDatos($nroLiqui, $periodoFiscal);
}

private function validarParametros($nroLiqui, $periodoFiscal)
{
    if (empty($nroLiqui) || empty($periodoFiscal)) {
        $this->dispatch('error-mapuche-mi-simplificacion', 'nroliqui o periodofiscal vacios');
        return false;
    }
    return true;
}

private function verificarYCrearTabla($instance, $table)
{
    $connection = $instance->getConnectionName();
    $status = Schema::connection($connection)->hasTable($table);
    if (!$status) {
        if (!$instance->createTable()) {
            $this->dispatch('error-mapuche-mi-simplificacion', 'La tabla MapucheMiSim no se creo');
            Log::info('La tabla MapucheMiSim no se creo');
            return false;
        }
        $this->dispatch('success-mapuche-mi-simplificacion', 'La tabla se creo exitosamente');
        Log::info('La tabla se creo exitosamente');
    }
    return true;
}

private function verificarYVaciarTabla($instance)
{
    $tableHasData = $instance->get();
    if ($tableHasData) {
        $this->dispatch('success-mapuche-mi-simplificacion', 'La tabla no esta vacia. Intentando vaciar');
        Log::info('La tabla no esta vacia. Intentando vaciar');
        AfipMapucheMiSimplificacion::truncate();
    }
}

private function insertarDatos($nroLiqui, $periodoFiscal)
{
    Log::info('mapucheMiSimplificacion() ');
    $result = TableModel::mapucheMiSimplificacion($nroLiqui, $periodoFiscal);
    if ($result) {
        $this->dispatch('success-mapuche-mi-simplificacion', 'insert into suc.afip_mapuche_mi_simplificacion exitoso');
    } else {
        $this->dispatch('error-mapuche-mi-simplificacion', 'insert into suc.afip_mapuche_mi_simplificacion fallo');
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
