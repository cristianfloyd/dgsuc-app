<div class="dark">
    <div class="p-2 space-y-4 bg-gray-900 min-h-screen">
        <div class="card bg-gray-800 shadow-xl rounded-lg overflow-hidden">
            <div class="card-header bg-gray-800 border-b border-gray-700 p-4">
                <h2 class="text-2xl font-bold text-white">Afip Mapuche Mi Simplificación</h2>
            </div>
            <div class="card-body bg-gray-800 p-4">
                {{ $this->table }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @filamentScripts
@endpush

@push('css')
    @filamentStyles
    <style>
        /* Estilos personalizados para el tema oscuro */
        .filament-tables-container {
            background-color: rgb(31 41 55) !important;
        }
        .filament-tables-header-cell {
            color: rgb(209 213 219) !important;
        }
        .filament-tables-row {
            background-color: rgb(17 24 39) !important;
        }
        .filament-tables-row:hover {
            background-color: rgb(55 65 81) !important;
        }
        .filament-tables-pagination {
            background-color: rgb(31 41 55) !important;
        }
        .filament-tables-pagination-records-per-page-selector {
            background-color: rgb(17 24 39) !important;
            color: rgb(209 213 219) !important;
        }
        .filament-tables-search-input {
            background-color: rgb(17 24 39) !important;
            color: rgb(209 213 219) !important;
        }
    </style>
@endpush
