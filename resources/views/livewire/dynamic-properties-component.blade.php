<div>
    <h3>Propiedades del Recurso</h3>
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
</div>

