<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use App\Filament\Resources\EmbargoResource;
use Filament\Tables\Concerns\InteractsWithTable;

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

    public function render()
    {
        return view('livewire.embargo-table');
    }
}
