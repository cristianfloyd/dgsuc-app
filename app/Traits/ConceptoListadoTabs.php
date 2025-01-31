<?php

namespace App\Traits;

use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

trait ConceptoListadoTabs
{
    public function getDefaultActiveTab(): string
    {
        return 'todos';
    }

    protected function getTabBadge(array $conceptos): int
    {
        return $this->getFilteredTableQuery()
            ->whereIn('codn_conce', $conceptos)
            ->count();
    }

    protected function getTabQuery(Builder $query, array $conceptos): Builder
    {
        return $query->whereIn('codn_conce', $conceptos);
    }

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos')
                ->badge(fn() => $this->getFilteredTableQuery()->count()),

            'dosuba' => Tab::make('DOSUBA')
                ->icon('heroicon-o-heart')
                ->badge(fn() => $this->getTabBadge(['1234', '1235', '1236']))
                ->modifyQueryUsing(fn (Builder $query) =>
                    $this->getTabQuery($query, ['1234', '1235', '1236'])
                ),

            'apuba' => Tab::make('APUBA')
                ->icon('heroicon-o-users')
                ->badge(fn() => $this->getTabBadge(['5678', '5679', '5680']))
                ->modifyQueryUsing(fn (Builder $query) =>
                    $this->getTabQuery($query, ['5678', '5679', '5680'])
                ),
        ];
    }
}
