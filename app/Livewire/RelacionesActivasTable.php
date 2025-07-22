<?php

namespace App\Livewire;

use App\Models\AfipRelacionesActivas;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

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

    #[On('datos-importados')]
    public function updateTable(): void
    {
        $this->resetPage();
    }

    public function setSortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'ASC' ? 'DESC' : 'ASC';
        } else {
            $this->sortField = $field;
            $this->sortDir = 'ASC';
        }
    }

    public function mount(): void
    {

    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.relaciones-activas-table', [
            'relacionesActivas' => AfipRelacionesActivas::search($this->search)
                ->orderBy($this->sortField, $this->sortDir)
                ->paginate($this->perPage),
        ]);
    }
}
