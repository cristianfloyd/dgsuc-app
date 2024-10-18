<div>
    <h3 class="text-lg font-medium">Current Property Values</h3>
    <dl class="mt-2 grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
        @foreach($properties as $property)
            <div class="sm:col-span-1">
                <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $property)) }}</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $this->getPropertyValues()[$property] }}</dd>
            </div>
        @endforeach
    </dl>
</div>
