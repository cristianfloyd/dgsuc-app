<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <span class="font-medium text-gray-500">Agente:</span>
            <p>{{ $cargo->dh01->nombre_completo }}</p>
        </div>

        <div>
            <span class="font-medium text-gray-500">CUIL:</span>
            <p>{{ $cargo->dh01->cuil }}</p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <span class="font-medium text-gray-500">Fecha Alta:</span>
            <p>{{ $cargo->fec_alta?->format('d/m/Y') }}</p>
        </div>

        <div>
            <span class="font-medium text-gray-500">Fecha Baja:</span>
            <p>{{ $cargo->fec_baja?->format('d/m/Y') ?? 'Sin baja' }}</p>
        </div>

        <div>
            <span class="font-medium text-gray-500">Estado:</span>
            <p>{{ $cargo->chkstopliq ? 'Bloqueado' : 'Activo' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <span class="font-medium text-gray-500">Categoría:</span>
            <p>{{ $cargo->dh11?->desc_categ }}</p>
        </div>

        <div>
            <span class="font-medium text-gray-500">Dependencia:</span>
            <p>{{ $cargo->dh30?->desc_uacad }}</p>
        </div>
    </div>
</div>
