<div>
    <div>
        <x-mary-form>
            <x-mary-select
                label="Archivo seleccionado: {{ $filename}}"
                :options="$listadoArchivos"
                option-label="original_name"
                placeholder="Seleccionar Archivo"
                placeholder-value="0" {{-- Set a value for placeholder. Default is `null` --}}
                hint="{{__('Select one, please.')}}"
                wire:model="selectedArchivoID" />
            <x-mary-button wire:click="seleccionarArchivo">
                Seleccionar archivo
            </x-mary-button>
            @if ($selectedArchivoID)
                <x-mary-button wire:click="importarArchivo">
                    Importar Archivo
                </x-mary-button>
            @endif
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
        </x-mary-form>
    </div>
</div>
