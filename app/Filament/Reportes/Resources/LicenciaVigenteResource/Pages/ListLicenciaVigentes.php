<?php

namespace App\Filament\Reportes\Resources\LicenciaVigenteResource\Pages;

use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Reportes\Resources\LicenciaVigenteResource;

class ListLicenciaVigentes extends ListRecords
{
    protected static string $resource = LicenciaVigenteResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Consulta de Licencias Vigentes';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Ingrese legajos para consultar sus licencias vigentes en el periodo fiscal actual';
    }

    protected function getHeaderActions(): array
    {
        return [
            // Sin acciones de creación
        ];
    }

    public function getTabs(): array
{
    return [
        'todas' => Tab::make('Todas'),
        'maternidad' => Tab::make('Maternidad')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('condicion', 5)),
        'excedencia' => Tab::make('Excedencia')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('condicion', 10)),
        'vacaciones' => Tab::make('Vacaciones')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('condicion', 12)),
        'ilt' => Tab::make('ILT')
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('condicion', [18, 19])),
        'proteccion_integral' => Tab::make('Protección Integral')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('condicion', 51)),
    ];
}
}
