<div>
    <div>
        <button wire:click="createTablePrueba" class="px-4 py-2 bg-blue-500 text-white rounded">
            Generar tabla AfipMapucheMiSimplificacion
        </button>
    </div>
    <div>
        <button wire:click="truncateTable" class="px-4 py-2 bg-blue-500 text-white rounded">
            Limpiar AfipMapucheMiSimplificacion
        </button>
    </div>

    <div>
        <p>
            {{ $prueba }}
        </p>
    </div>
</div>

