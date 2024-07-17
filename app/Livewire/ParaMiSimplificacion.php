<?php

namespace App\Livewire;

use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;

class ParaMiSimplificacion extends Component
{
    use WithPagination;

    public $nroLiqui;
    public $periodoFiscal;
    public $cuils = [];
    public $results = [];
    public $resultsUpdated = false;
    public $loading = true;
    protected $connection = 'pgsql-mapuche';


    #[On('compareCuils')]
    public function handleCompareCuils($nroLiqui, $periodoFiscal, $cuils)
    {
        $this->nroLiqui = $nroLiqui;
        $this->periodoFiscal = $periodoFiscal;
        $this->cuils = $cuils;
        $this->compareCuils();
    }

    public function compareCuils()
    {
        /**
         * Dispatch the "set-loading" event with a value of true, indicating that the component is loading.
         * This event can be listened to by other components or JavaScript code to display a loading indicator.
         */
        $this->loading = true;
        // $this->dispatch('loading-started');


        // Establecer la conexion a la base de datos con $connection
        $connection = DB::connection($this->connection);
        Log::info('conexion establecida',[$connection]);

        // Crear la tabla temporal
        $connection->statement('CREATE TABLE IF NOT EXISTS suc.temp_cuils (cuil CHAR(11));');
        if ($connection->getPdo()->errorCode() === '00000') {
            Log::info('Tabla temporal creada satisfactoriamente');
        } else {
            Log::error('Error al crear tabla temporal: ' . $connection->getPdo()->errorInfo()[2]);
        }
        // Insertar CUILs en la tabla temporal
        $connection->table('suc.temp_cuils')->insert(array_map(function($cuil) {
            return ['cuil' => $cuil];
        }, $this->cuils));


        //Antes de ejecutar la función SQL, asegúrate de que la tabla temporal esta creada y contiene los datos esperados.
        $tempCuils = $connection->select('SELECT * FROM suc.temp_cuils');
        // Logging para depuración
        Log::info('Datos en la tabla temporal:', $tempCuils);

        // Verificar la existencia de la función en la base de datos
        $functionExists = $connection->select("SELECT EXISTS (SELECT 1 FROM pg_proc WHERE proname = 'get_mi_simplificacion_tt')");
        Log::info('Existencia de la función:', $functionExists);

        // si tempCuils esta vacio, no ejecutar la función SQL y mostrar un mensaje de error
        if (empty($tempCuils)) {
            Log::error('La tabla temporal está vacía. No se ejecutará la función SQL.');
            // Puedes mostrar un mensaje de error en la vista o realizar otras acciones necesarias.
            dd('La tabla temporal está vacía. No se ejecutará la función SQL.');
            return;
        } else {
            log::info('La tabla temporal contiene datos. Se ejecutará la función SQL.');
            // Ejecutar la función SQL
            $this->results = $connection->select("SELECT *
                FROM suc.get_mi_simplificacion_tt(?, ?)",
                [$this->nroLiqui, $this->periodoFiscal]);

        }


        // Limpiar la tabla temporal (opcional)
        if($connection->statement('DROP TABLE IF EXISTS temp_cuils') === false)
        {
            Log::error('Error al eliminar la tabla temporal: ' . $connection->getPdo()->errorInfo()[2]);
        }   else {
            Log::info('Tabla temporal eliminada satisfactoriamente');
        }


        // Logging para depuración
        Log::info('Resultados de la función: Consulta Ejecutada!!!');
        // Forzar una actualización de la vista y hacer log

        $this->loading = false;
        $this->dispatch('results-updated');
    }


    // Método para actualizar la vista





    public function render()
    {
        $page = request()->get('page', 1); //Obtener la pagina actual
        $perPage = 10; //Cantidad de registros por pagina

        $collection = collect($this->results);

        $paginatedResults = new LengthAwarePaginator(
            $collection->forPage($page, $perPage),
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );


        return view('livewire.para-mi-simplificacion',[
            'paginatedResults' => $paginatedResults,
        ]);
    }
}
