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

    public $resultsUpdated = false;
    public $loading = true;
    protected $connection = 'pgsql-mapuche';



    public function render()
    {
        return view('livewire.para-mi-simplificacion');
    }
}
