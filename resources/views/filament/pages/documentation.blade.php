<x-filament-panels::page>
    <div class="flex flex-col lg:flex-row gap-4">
        {{-- Sidebar --}}
        <div class="lg:w-1/4">
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
        <div class="lg:w-3/4">
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

                {{-- Pestañas para cambiar entre vista HTML y Markdown --}}
                <div x-data="{ activeTab: 'rendered' }">
                    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                        <x-filament::tabs>
                            <x-filament::tabs.item
                                :active="true"
                                x-on:click="activeTab = 'rendered'"
                                icon="heroicon-m-eye"
                            >
                                Vista Previa
                            </x-filament::tabs.item>

                            <x-filament::tabs.item
                                x-on:click="activeTab = 'raw'"
                                icon="heroicon-m-code-bracket"
                            >
                                Markdown
                            </x-filament::tabs.item>
                        </x-filament::tabs>
                    </div>

                    @foreach($documentation as $doc)
                        {{-- Vista HTML Renderizada --}}
                        <div
                            x-show="$wire.activeSection === '{{ $doc['section'] }}' && activeTab === 'rendered'"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="prose dark:prose-invert max-w-none"
                        >
                            {!! $doc['rendered_content'] !!}
                        </div>

                        {{-- Vista Raw Markdown --}}
                        <div
                            x-show="$wire.activeSection === '{{ $doc['section'] }}' && activeTab === 'raw'"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="relative"
                        >
                            <pre class="language-markdown rounded-lg bg-gray-950 dark:bg-gray-900 p-4 overflow-x-auto">
                                <code class="text-sm text-gray-200">{{ $doc['content'] }}</code>
                            </pre>

                            {{-- Botón para copiar el contenido Markdown --}}
                            <button
                                x-data="{ copied: false }"
                                x-on:click="
                                    navigator.clipboard.writeText($el.parentElement.querySelector('code').textContent);
                                    copied = true;
                                    setTimeout(() => copied = false, 2000)
                                "
                                class="absolute top-2 right-2 p-2 text-gray-400 hover:text-gray-300 rounded-lg"
                            >
                                <span x-show="!copied">
                                    <x-heroicon-m-clipboard class="w-5 h-5" />
                                </span>
                                <span x-show="copied">
                                    <x-heroicon-m-clipboard-document-check class="w-5 h-5 text-green-500" />
                                </span>
                            </button>
                        </div>
                    @endforeach
                </div>
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

        /* Estilos adicionales para el código Markdown */
        pre.language-markdown {
            tab-size: 4;
            -moz-tab-size: 4;
        }

        pre.language-markdown code {
            white-space: pre-wrap;
            word-wrap: break-word;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
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
