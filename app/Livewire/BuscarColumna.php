<?php

namespace App\Livewire;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BuscarColumna extends Component
{
    use MapucheConnectionTrait;

    public $columnName;

    public $results;

    public function search(): void
    {
        $this->results = DB::connection($this->getConnectionName())
            ->table('information_schema.columns')
            ->select('table_schema', 'table_name', 'column_name')
            ->whereRaw('lower(column_name) like ?', ['%' . strtolower($this->columnName) . '%'])
            ->get();
    }

    public function render()
    {
        return view('livewire.buscar-columna', [
            'results' => $this->results,
        ]);
    }
}
