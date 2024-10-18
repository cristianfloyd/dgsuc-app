<dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
    @foreach($properties as $key => $value)
        <div class="sm:col-span-1">
            <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
            <dd class="mt-1 text-sm text-gray-900">
                @if(is_bool($value))
                    {{ $value ? 'Yes' : 'No' }}
                @elseif(is_array($value))
                    {{ implode(', ', $value) }}
                @else
                    {{ $value }}
                @endif
            </dd>
        </div>
    @endforeach
</dl>
