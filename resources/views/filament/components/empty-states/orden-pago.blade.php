<div class="flex flex-col items-center justify-center space-y-6 px-6 py-20">
    <div class="flex flex-col items-center justify-center space-y-3">
        <x-heroicon-o-document-text class="h-16 w-16 text-gray-400" />

        <div class="text-center">
            <h2 class="text-xl font-bold tracking-tight">{{ $title }}</h2>
            <p class="text-sm text-gray-500">{{ $description }}</p>
        </div>

        <ul class="mt-4 list-inside list-decimal text-sm text-gray-500">
            @foreach($steps as $step)
                <li>{{ $step }}</li>
            @endforeach
        </ul>

        <x-filament::button
            color="primary"
            tag="a"
            :href="$action['url']"
        >
            {{ $action['label'] }}
        </x-filament::button>
    </div>
</div>
