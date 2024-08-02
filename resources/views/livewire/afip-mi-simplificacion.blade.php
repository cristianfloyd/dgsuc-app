<div>
    <div>
    {{-- <div x-data="{ ShowMessage: false, message: '' }"
        x-show="ShowMessage"
        x-on:show-message.window="
            ShowMessage = true;
            message = $wire.message;
            setTimeout(() => ShowMessage = false, 3000);
            console.log($wire.message)"
        class="alert alert-success text-center" wire:loading.remove role="alert">
        <p x-text="message"></p>
        <button @click="ShowMessage = false" class="btn btn-neutral">X</button> --}}
        {{$message}}
    </div>

    <div class="mb-4 container" id="process-container">
        <h1>Proceso de AFIP Mi Simplificación</h1>
        <div class="mb-4">
            <button class="btn btn-primary btn-info" wire:click="startProcess" @if (!$processFinished) disabled

            @endif>
                Iniciar Proceso
            </button>
            <button type="button" class="btn btn-neutral" wire:click="endProcess"  @if ($processFinished) disabled

            @endif>
                Terminar Proceso
            </button>
        </div>
        <h2>Pasos del Proceso:</h2>
        <ul>
            @foreach ($steps as $stepKey => $stepName)
                <li>
                    @if ($currentProcess->steps[$stepKey] === 'completed')
                        ✅
                    @elseif($stepKey === $currentStep)
                        ➡️
                        <button type="button" class="btn btn-accent"
                            wire:click = "markStepAsCompleted('{{ $stepKey }}')">
                            Completar Paso
                        </button>
                    @endif
                    {{ $stepName }}

                </li>
            @endforeach
        </ul>
        @if ($processFinished)
            <div class="mt-4">
                <button type="button" wire:click="showParaMiSimplificacion" class="btn btn-success">
                    Mostrar Mi Simplificación
                </button>
            </div>
        @endif
    </div>
    @if ($ParaMiSimplificacion)
        <div class="mt-4">
            <livewire:para-mi-simplificacion />
        </div>
    @endif
</div>
