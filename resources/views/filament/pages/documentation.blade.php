<x-filament::page>
    <div class="grid grid-cols-12 gap-4">
        {{-- Sidebar mejorado con indicador activo --}}
        <div class="col-span-3">
            <div class="sticky top-4">
                <nav class="space-y-1">
                    @foreach($sections as $key => $label)
                        <a href="#{{ $key }}"
                           class="block p-2 text-sm rounded-lg transition-colors
                                  {{ $activeSection === $key
                                     ? 'bg-primary-500 text-white'
                                     : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}"
                           wire:click="setActiveSection('{{ $key }}')">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>

        {{-- Contenido principal con scroll spy --}}
        <div class="col-span-9">
            <div class="prose max-w-none dark:prose-invert"
                 x-data="{ activeSection: @entangle('activeSection') }"
                 x-on:scroll.window="updateActiveSection">
                @foreach($documentation as $doc)
                    <section id="{{ $doc->section }}"
                             class="scroll-mt-16">
                        <h2>{{ $doc->title }}</h2>
                        {!! $doc->rendered_content !!}
                    </section>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Barra de acciones flotante --}}
    <div class="fixed bottom-4 right-4 flex gap-2">
        <x-filament::button
            icon="heroicon-o-download"
            href="{{ route('documentation.download') }}"
            class="shadow-lg"
        >
            Descargar PDF
        </x-filament::button>

        <x-filament::button
            icon="heroicon-o-printer"
            wire:click="print"
            class="shadow-lg"
        >
            Imprimir
        </x-filament::button>
    </div>
</x-filament::page>
