<div>
    <x-filament::section>
        <div>
            <h6>Propiedades del Recurso</h6>
        </div>
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
