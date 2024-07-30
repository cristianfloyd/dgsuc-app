<div>
    <h1>Proceso de AFIP Mi Simplificación</h1>

    <div class="mb-4 container" id="process-container">
        <div class="mb-4">
            <button wire:click="startProcess" class="btn btn-primary" @if($processFinished) disabled @endif>
                Iniciar Proceso
            </button>
            <button wire:click="endProcess" class="btn btn-danger" @if($processFinished) disabled @endif>
                Terminar Proceso
            </button>
        </div>
        <h2>Pasos del Proceso:</h2>
        <ul>
            @foreach($steps as $stepKey => $stepName)
                <li>
                    @if($currentProcess->steps[$stepKey] === 'completed')
                        ✅
                    @elseif($stepKey === $currentStep)
                        ➡️
                    @endif
                    {{ $stepName }}
                </li>
            @endforeach
        </ul>
        @if($processFinished)
            <div class="mt-4">
                <button wire:click="showParaMiSimplificacion)" class="btn btn-success">
                    Mostrar Para Mi Simplificación
                </button>
            </div>
        @endif
    </div>

    @if ($showParaMiSimplificacion)
        <div class="mt-4">
            <livewire:para-mi-simplificacion />
        </div>
    @endif
</div>
