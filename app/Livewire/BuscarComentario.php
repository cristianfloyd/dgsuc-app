<?php

namespace App\Livewire;

use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BuscarComentario extends Component
{
    use WithPagination;
    use MapucheConnectionTrait;

    #[Url('descripcion')]
    public $description = '';

    // public $filter=['columns', 'tables', 'both'];
    public $setFilter = 'both';

    public $perPage = 15;

    public function updatingDescription(): void
    {
        $this->resetPage();
        $this->reset();
    }

    public function render()
    {
        $columnComments = [];
        $tableComments = [];
        if (!empty($this->description)) {
            $columnComments = DB::connection($this->getConnectionName())
                ->table('information_schema.columns as cols')
                ->join('pg_catalog.pg_class as c', 'c.relname', '=', 'cols.table_name')
                ->join('pg_catalog.pg_namespace as name', function ($join): void {
                    $join->on('name.oid', '=', 'c.relnamespace')
                        ->on('name.nspname', '=', 'cols.table_schema');
                })
                ->join('pg_catalog.pg_attribute as a', function ($join): void {
                    $join->on('a.attrelid', '=', 'c.oid')
                        ->on('a.attname', '=', 'cols.column_name');
                })
                ->whereNotIn('name.nspname', ['pg_catalog', 'information_schema'])
                ->where(DB::raw('col_description((c.oid::regclass), cols.ordinal_position::int)'), 'LIKE', '%' . $this->description . '%')
                ->orWhere('cols.column_name', 'ILIKE', '%' . $this->description . '%')
                ->orderBy('cols.table_name')
                ->orderBy('cols.column_name')
                ->select([
                    'cols.table_schema',
                    'cols.table_name',
                    'cols.column_name',
                    DB::raw('col_description(c.oid, cols.ordinal_position::int) AS comment'),
                    DB::raw("'column' AS type"),
                ]);

            $tableComments = DB::connection($this->getConnectionName())
                ->table('pg_catalog.pg_class as c')
                ->join('pg_catalog.pg_namespace as n', 'n.oid', '=', 'c.relnamespace')
                ->select(
                    'n.nspname as table_schema',
                    'c.relname as table_name',
                    DB::raw('NULL as column_name'),
                    DB::raw('obj_description(c.oid) as comment'),
                    DB::raw("'table' as type"),
                )
                ->whereNotIn('n.nspname', ['pg_catalog', 'information_schema'])
                ->where(DB::raw('obj_description(c.oid)'), 'LIKE', '%' . $this->description . '%');



            if ($this->setFilter == 'columns') {
                $query = $columnComments->paginate($this->perPage);
            } elseif ($this->setFilter == 'tables') {
                $query = $tableComments->paginate($this->perPage);
            } else {
                $query = $columnComments->unionAll($tableComments)
                    ->orderBy('table_schema')
                    ->orderBy('table_name')
                    ->orderBy('column_name');
                $query = $query->paginate($this->perPage);
            }
        }

        return view('livewire.buscar-comentario', [
            'results' => $query ?? [],
        ]);
    }
}
