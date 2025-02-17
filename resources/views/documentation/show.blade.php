<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Barra de búsqueda -->
            <div class="mb-6">
                <livewire:documentation-search />
            </div>

            <div class="lg:grid lg:grid-cols-4 lg:gap-8">
                <!-- Tabla de Contenidos Lateral -->
                <div class="hidden lg:block">
                    <div class="sticky top-8">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                Contenido
                            </h3>
                            <nav class="space-y-1">
                                @foreach($tableOfContents as $item)
                                    <a href="#{{ $item['id'] }}"
                                       class="block text-sm transition-colors duration-200 relative
                                            {{ $item['level'] === 2 ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400' }}
                                            {{ $item['level'] === 3 ? 'ml-4' : '' }}
                                            {{ $item['level'] === 4 ? 'ml-8' : '' }}
                                            hover:text-blue-600 dark:hover:text-blue-400">
                                        {{ $item['title'] }}
                                    </a>
                                @endforeach
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Contenido Principal -->
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="prose dark:prose-invert max-w-none">
                                {!! $content !!}
                            </div>

                            <!-- Navegación entre documentos -->
                            <div class="mt-8 flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                                @if($previousDoc)
                                    <a href="{{ route('documentation.show', $previousDoc->slug) }}"
                                       class="flex items-center text-blue-600 dark:text-blue-400 hover:underline">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                        {{ $previousDoc->title }}
                                    </a>
                                @else
                                    <div></div>
                                @endif

                                @if($nextDoc)
                                    <a href="{{ route('documentation.show', $nextDoc->slug) }}"
                                       class="flex items-center text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $nextDoc->title }}
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll para los enlaces de la tabla de contenidos
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
