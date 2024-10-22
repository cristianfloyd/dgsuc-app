<div>
    <x-filament::section>
        <div>
            <h6>Propiedades del Recurso</h6>
        </div>
        <ul>
            @foreach ($properties as $key => $values)
                @if ($key === 'periodoFiscal' && is_array($values))
                    <li>
                        @foreach ($values as $key2 => $value)
                            <strong>{{ __("$key2") }}: </strong> {{ $value }}
                        @endforeach
                    </li>
                    @continue
                @endif
                <li>
                    <strong>{{ $key }}:</strong>
                    @if (is_array($values) && $key != 'periodoFiscal')
                        {{ json_encode($values) }}
                    @else
                        {{ $values }}
                    @endif
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</div>
