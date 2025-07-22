<?php

namespace App\Livewire;

use App\Filament\Resources\EmbargoResource;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class EmbargoTable extends Component implements HasTable
{
    use InteractsWithTable;

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

    protected function getTableQuery()
    {
        return EmbargoResource::getEloquentQuery();
    }

    protected function getTableColumns(): array
    {
        return EmbargoResource::table(new Table($this))->getColumns();
    }
}
