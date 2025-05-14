<?php

namespace App\Filament\Reportes\Resources\LicenciaVigenteResource\Pages;

use Illuminate\Support\Facades\Log;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Reportes\Resources\LicenciaVigenteResource;

class ListLicenciaVigentes extends ListRecords
{
    protected static string $resource = LicenciaVigenteResource::class;

    /**
     * Hook que se ejecuta cuando el componente se monta
     */
    public function mount(): void
    {
        parent::mount();
        
        // Verificar si hay legajos en la sesión
        $legajos = session('licencias_vigentes_legajos', []);
        
        if (!empty($legajos)) {
            Log::info('Montando componente con legajos en sesión', [
                'legajos_count' => count($legajos)
            ]);
        } else {
            Log::info('Montando componente sin legajos en sesión');
        }
    }
    
    // /**
    //  * Hook que se ejecuta después de que el componente se ha actualizado
    //  */
    // public function updated($property, $value): void
    // {
    //     parent::updated($property);
        
    //     // Si se actualiza alguna propiedad relacionada con la tabla, verificamos los legajos
    //     if (str_starts_with($property, 'tableFilters') || 
    //         str_starts_with($property, 'tableSortColumn') || 
    //         str_starts_with($property, 'tableSearchQuery')) {
            
    //         $legajos = session('licencias_vigentes_legajos', []);
            
    //         if (empty($legajos)) {
    //             Log::info('No hay legajos en sesión después de actualizar: ' . $property);
    //         }
    //     }
    // }

    /**
     * Método para refrescar la tabla manteniendo los legajos en sesión
     */
    public function refreshTable(): void
    {
        $this->resetTable();
    }



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
