<?php

namespace App\Livewire;

use App\Models\AfipMapucheMiSimplificacion;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class ParaMiSimplificacion extends Component
{

    public function mount(){
        $this->dispatch('para-mi-simplificacion-mount');
    }

    #[Computed()]
    public function headers(): array
    {
        // Get the headers from the database or from the model AfipMapucheMiSimplificacion
        $instance = new AfipMapucheMiSimplificacion();
        $headers = $instance->getTableHeaders();
        return $headers;
    }
    public function render()
    {
        $dataTable = AfipMapucheMiSimplificacion::query()->paginate(10);

        return view('livewire.para-mi-simplificacion',[
            'dataTable' => $dataTable,
        ]);
    }
}
