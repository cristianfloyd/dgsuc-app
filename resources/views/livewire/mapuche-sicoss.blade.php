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
    @if ($latestWorkflow = $this->workflowService->getLatestWorkflow())
        <div class="mt-4">
            <h3>Progreso del Workflow</h3>
            <ul>
                @foreach ($this->workflowService->getSteps() as $step => $description)
                    <li>
                        {{ $description }}:
                        @if ($this->workflowService->isStepCompleted($latestWorkflow, $step))
                            <span class="text-green-500">Completado</span>
                        @elseif ($step === 'import_archivo_mapuche')
                            <span class="text-blue-500">En progreso</span>
                        @else
                            <span class="text-gray-500">Pendiente</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
