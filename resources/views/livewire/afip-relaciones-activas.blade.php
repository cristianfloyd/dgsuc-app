<div class="container mx-auto p-4">
    <div class="w-1/2 items-center mx-auto card bg-neutral text-neutral-content">
        <div class="card-body">
            <details class="collapse">
                <summary class="collapse-title text-xl font-medium cursor-pointer">
                    Importar Relaciones Activas
                </summary>
                <div class="collapse-content">
                    <div class="card bg-base-100 text-base-content">
                        <div class="card-body items-center text-center">
                            <h2 class="card-title">Importar Relaciones Activas</h2>
                            <p>Seleccione el archivo a importar.</p>
                            <form action="submit" wire:submit="importar">
                                <x-mary-select
                                    label="Seleccionar archivo"
                                    icon="o-document-arrow-up"
                                    :options="$archivosCargados"
                                    option-value="id"
                                    option-label="original_name"
                                    placeholder=" ... "
                                    placeholder-value="0"
                                    hint="Seleccione uno."
                                    wire:model.live="archivoSeleccionadoId"
                                />
                                <x-mary-button type="submit" label="Importar" class="mt-4" />
                            </form>
                        </div>
                    </div>
                </div>
            </details>
        </div>
    </div>

    <div class="mt-8">
        <livewire:relaciones-activas-table />
    </div>
</div>

