<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ParaMiSimplificacion extends Component
{
    public $nro_liqui;
    public $periodo_fiscal;
    public $cuils = [];
    public $results = [];
    protected $connection = 'pgsql-mapuche';

    protected $listeners = ['compareCuils' => 'handleCompareCuils'];

    public function handleCompareCuils($nro_liqui, $periodo_fiscal, $cuils)
    {
        $this->nro_liqui = $nro_liqui;
        $this->periodo_fiscal = $periodo_fiscal;
        $this->cuils = explode(',', $cuils);

        $this->compareCuils();
    }

    public function compareCuils()
    {

        // Crear la tabla temporal
        DB::statement('CREATE TEMP TABLE temp_cuils (cuil CHAR(11));');

        // Insertar CUILs en la tabla temporal
        $insertQuery = 'INSERT INTO temp_cuils (cuil) VALUES ' . implode(',', array_map(function($cuil) {
            return "('$cuil')";
        }, $this->cuils));
        DB::statement($insertQuery);

        // Ejecutar la función SQL
        $this->results = DB::select("SELECT * FROM suc.get_mi_simplificacion(?, ?)", [$this->nro_liqui, $this->periodo_fiscal]);

        // Limpiar la tabla temporal (opcional)
        DB::statement('DROP TABLE IF EXISTS temp_cuils');
    }


    public function render()
    {
        return view('livewire.para-mi-simplificacion',[
            'results' => $this->results
        ]);
    }
}
