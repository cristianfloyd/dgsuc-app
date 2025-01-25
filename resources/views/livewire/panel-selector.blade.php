<div class="p-6">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @foreach ($panels as $panel)
            <a href="{{ $panel['url'] }}"
                class="flex items-center p-4 bg-slate-800 rounded-lg shadow-md hover:bg-gray-600">
                <x-dynamic-component
                    :component="$panel['icon']"
                    class="w-6 h-6 text-primary-600"
                />
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-200">
                        {{ $panel['name'] }}
                    </h3>
                </div>
            </a>
        @endforeach
    </div>
</div>
