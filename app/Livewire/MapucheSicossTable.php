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


    /**
     * Recupera de forma segura el valor del $attribute especificado del $model.
     *
     * Si el valor del attribute es un objeto, intentará convertirlo en una cadena.
     * usando el método `__toString()`. Si eso falla, devolverá el nombre de la clase.
     * del objeto.
     *
     * Si ocurre una excepción al recuperar el valor del attribute, devolverá un
     * mensaje de error con el mensaje de excepción.
     * @param mixed $model The model instance to retrieve the attribute from.
     * @param string $attribute The name of the attribute to retrieve.
     * @return mixed The value of the specified attribute, or an error message if an exception occurs.
     */
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
