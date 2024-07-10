<div>
    @if(session('tableIsEmpty'))
        <div class="alert alert-danger">
            {{ session('tableIsEmpty') }}
        </div>
    @endif
    @if (session('tableIsNotEmpty'))
        <div class="alert alert-success">
            {{ session('tableIsNotEmpty') }}
        </div>
    @endif
    @if ($tablasVacias)

    <x-mary-button wire:click="verificarTablas()" label="Verificar Tablas">
        Verificar
    </x-mary-button>
    @endif
    @if (!$tablasVacias)
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
            <x-mary-button wire:click="seleccionarArchivo()">
                Seleccionar archivo
            </x-mary-button>
            @if ($selectedArchivoID)
                <x-mary-button wire:click="importarArchivo()">
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


    <div class="flex">

    </div>
    @endif
    <!-- Mostrar mensajes flash con Alpine.js -->
    @if (session()->has('success'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 5000)"
            x-show="show"
            class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 5000)"
            x-show="show"
            class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
</div>
