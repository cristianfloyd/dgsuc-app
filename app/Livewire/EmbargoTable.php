<?php

namespace App\Livewire;

use Sushi\Sushi;
use Livewire\Component;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use App\Filament\Resources\EmbargoResource;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Support\Contracts\TranslatableContentDriver;

class EmbargoTable extends Component implements HasTable
{
    use InteractsWithTable;



    protected function getTableQuery()
    {
        return EmbargoResource::getEloquentQuery();
    }

    protected function getTableColumns(): array
    {
        return EmbargoResource::table(new Table($this))->getColumns();
    }



    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        // Aquí puedes devolver una instancia de un controlador de contenido traducible
        // o null si no necesitas soporte de traducción.
        return null;
    }

    public function render()
    {
        return view('livewire.embargo-table');
    }
}
