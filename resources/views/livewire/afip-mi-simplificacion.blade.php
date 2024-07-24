<div>
    <h1>Proceso de AFIP Mi Simplificación</h1>
    <ul>
        @foreach($steps as $stepKey => $stepName)
            <li>
                @if($processLog->steps[$stepKey] === 'completed')
                    ✅
                @elseif($stepKey === $currentStep)
                    ➡️
                @endif
                <button wire:click="goToStep('{{ $stepKey }}')"
                        @if($processLog->steps[$stepKey] !== 'completed' && $stepKey !== $currentStep) disabled @endif>
                    {{ $stepName }}
                </button>
            </li>
        @endforeach
    </ul>
</div>
