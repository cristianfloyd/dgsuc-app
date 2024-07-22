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
use Livewire\WithPagination;

class ParaMiSimplificacion extends Component
{
    public $prueba;


    public function createTablePrueba()
    {
        Log::info('entro');
        $instance = new AfipMapucheMiSimplificacion();
        Log::info("{$instance} created");
        $this->prueba = $instance->createTable();
    }

    public function truncateTable()
    {
        Log::info('truncando tabla');
        AfipMapucheMiSimplificacion::truncate();
        $instance = new AfipMapucheMiSimplificacion();
        // verificar si la tabla existe
    }

    public function borrarTable()
    {
        Log::info('borrando tabla');
        AfipMapucheMiSimplificacion::dropIfExists();
        $instance = new AfipMapucheMiSimplificacion();
        // verificar si la tabla existe
    }

    public function render()
    {
        return view('livewire.para-mi-simplificacion');
    }
}
