<div>

    <div>
        @foreach($messages as $message)
            <div x-data="{ show: true }"
                x-show="show"
                x-init="setTimeout(() => show = false, 3000)"
                class="alert alert-{{ $message['type'] }} text-center"
                role="alert">
                <p>{{ $message['message'] }}</p>
                <button @click="show = false" class="btn btn-neutral">X</button>
            </div>
        @endforeach

        <!-- Resto del contenido -->
    </div>
    @dump($this->processFinished)
    <div class="mb-4 container" id="process-container">
        <h1>Proceso de AFIP Mi Simplificación</h1>
        <div class="mb-4">
            <button class="btn btn-primary btn-info" wire:click="startProcess" @if (!$processFinished) disabled @endif>
                Iniciar
            </button>
            @if (!$processFinished)
                <button type="button" class="btn btn-neutral" wire:click="endProcess">
                    Terminar
                </button>
            @endif

            @if ($this->showResetButton)
                <button type="button" class="btn btn-warning" wire:click="resetWorkflow">
                    Reiniciar
                </button>
            @endif
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
                            wire:click = "goToCurrentStep('{{ $stepKey }}')">
                            Completar Paso
                        </button>
                    @endif
                    {{ $stepName }}

                </li>
            @endforeach
        </ul>
        @if ($processFinished)
            <div class="mt-4">
                <x-mary-button type="button" wire:click="toggleParaMiSimplificacion" class="btn btn-success">
                    {{ $ParaMiSimplificacion ? 'Ocultar' : 'Mostrar' }} resultado
                </x-mary-button>
            </div>
        @endif
    </div>
    @if ($ParaMiSimplificacion)
        <div class="mt-4">
            <livewire:para-mi-simplificacion />
        </div>
    @endif
</div>
