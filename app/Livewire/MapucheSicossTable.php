<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AfipMapucheSicoss;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Url;

class MapucheSicossTable extends Component
{
    use WithPagination;

    #[Url(as: 's')]
    public $search = '';
    #[Url(as: 'p')]
    public $perPage = 5;

    protected $paginationTheme = 'bootstrap';

    public function mount(){
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = AfipMapucheSicoss::search($this->search);
        $data = $query->paginate($this->perPage);

        // Obtener los nombres de las columnas
        $columns = Schema::connection('pgsql-mapuche')->getColumnListing('suc.afip_mapuche_sicoss');

        // Convertir los datos a un array de manera segura
        $dataArray = $data->map(function ($item) use ($columns) {
            $row = [];
            foreach ($columns as $column) {
                $row[$column] = $this->getAttributeSafely($item, $column);
            }
            // Agregar una clave única para cada fila
            $row['_key'] = $item->getKeyString();
            return $row;
        });

        return view('livewire.mapuche-sicoss-table', [
            'data' => $data,
            'dataArray' => $dataArray,
            'columns' => $columns,
        ]);
    }

    private function getAttributeSafely($model, $attribute)
    {
        try {
            $value = $model->getRawOriginal($attribute);
            if (is_object($value)) {
                return method_exists($value, '__toString') ? (string)$value : get_class($value);
            }
            return $value;
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
