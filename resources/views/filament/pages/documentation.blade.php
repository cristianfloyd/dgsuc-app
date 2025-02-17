<x-filament-panels::page>
    <div class="grid grid-cols-12 gap-6">
        {{-- Sidebar con lista de documentos --}}
        <div class="col-span-12 lg:col-span-3">
            <x-filament::section>
                <x-slot name="heading">
                    Secciones
                </x-slot>

                <div class="space-y-2">
                    @foreach($sections as $section)
                        <x-filament::button
                            wire:click="setActiveSection('{{ $section['key'] }}')"
                            :color="$activeSection === $section['key'] ? 'primary' : 'gray'"
                            :variant="$activeSection === $section['key'] ? 'filled' : 'outlined'"
                            class="w-full justify-start"
                        >
                            {{ $section['title'] }}
                        </x-filament::button>
                    @endforeach
                </div>
            </x-filament::section>
        </div>

        {{-- Contenido principal --}}
        <div class="col-span-12 lg:col-span-9">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between">
                        <span>
                            {{ collect($sections)->firstWhere('key', $activeSection)['title'] ?? 'Documentación' }}
                        </span>

                        <x-filament::button
                            wire:click="print"
                            icon="heroicon-m-printer"
                            size="sm"
                        >
                            Imprimir
                        </x-filament::button>
                    </div>
                </x-slot>

                @foreach($documentation as $doc)
                    <div
                        x-show="$wire.activeSection === '{{ $doc['section'] }}'"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="prose dark:prose-invert max-w-none"
                    >
                        {!! $doc['rendered_content'] !!}
                    </div>
                @endforeach
            </x-filament::section>
        </div>
    </div>

    {{-- Estilos específicos para la documentación --}}
    <style>
        .prose h1, .prose h2, .prose h3, .prose h4 {
            scroll-margin-top: 100px;
        }

        .heading-permalink {
            opacity: 0;
            transition: opacity 0.2s;
            margin-right: 0.5em;
            text-decoration: none;
        }

        .prose :where(h1, h2, h3, h4):hover .heading-permalink {
            opacity: 1;
        }

        /* Estilos para bloques de código */
        .prose :where(pre) {
            @apply bg-gray-950 dark:bg-gray-900 rounded-lg;
            margin: 0;
            padding: 1rem;
        }

        /* Estilos para tablas */
        .prose :where(table) {
            @apply w-full;
        }

        .prose :where(thead) {
            @apply bg-gray-50 dark:bg-gray-800;
        }

        .prose :where(th, td) {
            @apply p-2 border dark:border-gray-700;
        }

        @media print {
            .fi-sidebar,
            .fi-header,
            .fi-btn {
                display: none !important;
            }

            .prose {
                max-width: none !important;
            }
        }
    </style>

    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('print-documentation', () => {
                    window.print();
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
