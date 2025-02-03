<?php

namespace App\Traits;

use Filament\Resources\Components\Tab;
use App\Services\ConceptoListadoService;
use Illuminate\Database\Eloquent\Builder;
use App\Services\ConceptosSindicatosService;

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
                ->badge(fn() => $this->getTabBadge(ConceptosSindicatosService::getDosubaCodigos()))
                ->modifyQueryUsing(fn (Builder $query) =>
                    $this->getTabQuery($query, ConceptosSindicatosService::getDosubaCodigos())
                ),

            'apuba' => Tab::make('APUBA')
                ->icon('heroicon-o-users')
                ->badge(fn() => $this->getTabBadge(ConceptosSindicatosService::getApubaCodigos()))
                ->modifyQueryUsing(fn (Builder $query) =>
                    $this->getTabQuery($query, ConceptosSindicatosService::getApubaCodigos())
                ),
            'aduba' => Tab::make('ADUBA')
                ->icon('heroicon-o-users')
                ->badge(fn() => $this->getTabBadge(ConceptosSindicatosService::getAdubaCodigos()))
                ->modifyQueryUsing(fn (Builder $query) =>
                $this->getTabQuery($query, ConceptosSindicatosService::getAdubaCodigos())
                ),
        ];
    }
}
