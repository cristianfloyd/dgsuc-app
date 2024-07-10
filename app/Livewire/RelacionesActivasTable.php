<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use App\Models\AfipRelacionesActivas;

class RelacionesActivasTable extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';
    #[Url(history: true)]
    public $perPage = 5;
    #[Url(history: true)]
    public $sortField = 'id';
    #[Url(history: true)]
    public $sortDir = 'DESC';
    #[Url(history: true)]

    public function setSortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'ASC' ? 'DESC' : 'ASC';
        } else {
            $this->sortField = $field;
            $this->sortDir = 'ASC';
        }
    }

    public function mount()
    {
        //
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.relaciones-activas-table',[
            'relacionesActivas' => AfipRelacionesActivas::search($this->search)
                ->orderBy($this->sortField, $this->sortDir)
                ->paginate($this->perPage),
        ]);
    }
}
