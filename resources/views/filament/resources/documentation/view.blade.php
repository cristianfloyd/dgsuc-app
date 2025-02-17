<x-filament::page>
    <div class="prose max-w-none dark:prose-invert">
        {!! $documentation !!}
    </div>

    <x-filament::button
        class="mt-4"
        icon="heroicon-o-download"
        href="{{ route('documentation.download') }}"
    >
        Descargar PDF
    </x-filament::button>
</x-filament::page>
