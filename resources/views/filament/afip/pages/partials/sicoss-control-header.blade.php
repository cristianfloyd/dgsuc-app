<div class="flex items-center justify-between gap-4 p-2">
    <div>
        <h2 class="text-lg font-bold">Controles SICOSS - {{ $year }}/{{ $month }}</h2>
        <p class="text-sm text-gray-500">Panel de gestión y búsqueda avanzada</p>
    </div>
    <div>
        {{-- Aquí puedes poner un formulario de búsqueda, filtros, etc. --}}
        <form wire:submit.prevent="buscar">
            <input type="text" wire:model.defer="search" placeholder="Buscar CUIL, nombre, etc..." class="input input-bordered" />
            <button type="submit" class="btn btn-primary ml-2">Buscar</button>
        </form>
    </div>
</div>