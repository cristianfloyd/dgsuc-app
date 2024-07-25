<div>
    <details class="collapse mx-auto mt-2 card w-96 bg-neutral text-neutral-content">
        <summary class="collapse-title text-xl font-medium">open/close</summary>
        <div class="collapse-content">
            <div class="mx-auto mt-2 card w-96 bg-neutral text-neutral-content">
                <div class="card-body items-center text-center">
                    <h2 class="card-title">Importar Relaciones Activas</h2>
                    <p>Seleccione el archivo a importar.</p>
                </div>
                <form action="submit" wire:submit="importar">
                    <x-mary-select label="Seleccionar archivo" icon="o-document-arrow-up" :options="$archivosCargados" option-value="id"
                        option-label="original_name" placeholder=" ... " placeholder-value="0"
                        hint="Seleccione uno." wire:model.live="archivoSeleccionadoId" />
                    <x-mary-button type="submit" label="Importar" />
                </form>
            </div>
        </div>
    </details>
    {{-- llamar al componente livewire RelacionesActivasTable --}}
    <livewire:relaciones-activas-table />
</div>
