<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Traits\MapucheConnectionTrait;

class BuscarColumna extends Component
{
    use MapucheConnectionTrait;

    public $columnName;
    public $results;

    public function search()
    {
        $this->results = DB::connection($this->getConnectionName())
            ->table('information_schema.columns')
            ->select('table_schema','table_name', 'column_name')
            ->whereRaw('lower(column_name) like ?', ['%' . strtolower($this->columnName) . '%'])
            ->get();
    }

    public function render()
    {
        return view('livewire.buscar-columna', [
            'results' => $this->results
        ]);
    }
}
