<div>
    <x-filament::section >
        <x-slot name="heading">
            <h4>Propiedades del Recurso</h4>
        </x-slot>
        <ul>
            @foreach($properties as $key => $value)
            <li>
                <strong>{{ $key }}:</strong>
                @if(is_array($value))
                    {{ json_encode($value) }}
                @else
                    {{ $value }}
                @endif
            </li>
            @endforeach
        </ul>
    </x-filament::section>
</div>
