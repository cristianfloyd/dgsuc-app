<?php

namespace App\Livewire;

use App\Models\dh21 as ModelsDh21;
use Livewire\Component;
use Livewire\WithPagination;

class Dh21 extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 5;

    public function updatingSearch(){
        $this->resetPage();
    }

    public function render(){
        return view('livewire.dh21',[
            'dh21s' => ModelsDh21::search($this->search)
                ->orderBy('nro_legaj', 'desc')
                ->paginate($this->perPage)]
            );
    }
}
