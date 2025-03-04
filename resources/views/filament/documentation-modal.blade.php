<div class="filament-documentation-modal">
    <div class="flex gap-6">
        {{-- Sidebar con índice --}}
        <div class="hidden lg:block w-64 shrink-0">
            <div class="sticky top-0 pt-4 max-h-[calc(100vh-2rem)] overflow-y-auto">
                <nav class="space-y-1">
                    <p class="font-medium text-sm text-gray-500 dark:text-gray-400 mb-3">CONTENIDO</p>
                    <div id="toc" class="space-y-2 text-sm"></div>
                </nav>
            </div>
        </div>

        {{-- Contenido principal --}}
        <div class="flex-1 min-w-0">
            <div class="prose dark:prose-invert max-w-none">
                <style>
                    /* Estilos específicos para la documentación */
                    .filament-documentation-modal .prose {
                        font-size: 0.95rem;
                        line-height: 1.6;
                    }

                    .filament-documentation-modal .prose h1 {
                        font-size: 1.875rem;
                        color: rgb(var(--primary-600));
                        margin-bottom: 1rem;
                        padding-bottom: 0.5rem;
                        border-bottom: 1px solid rgb(var(--gray-200));
                    }

                    .filament-documentation-modal .prose h2 {
                        font-size: 1.5rem;
                        margin-top: 2rem;
                        color: rgb(var(--gray-900));
                        scroll-margin-top: 5rem;
                    }

                    .filament-documentation-modal .prose h3 {
                        font-size: 1.25rem;
                        margin-top: 1.5rem;
                        color: rgb(var(--gray-800));
                    }

                    .filament-documentation-modal .prose p {
                        margin-top: 0.75rem;
                        margin-bottom: 0.75rem;
                    }

                    .filament-documentation-modal .prose ul {
                        margin-top: 0.5rem;
                        margin-bottom: 0.5rem;
                    }

                    .filament-documentation-modal .prose li {
                        margin-top: 0.25rem;
                        margin-bottom: 0.25rem;
                    }

                    .filament-documentation-modal .prose blockquote {
                        border-left-color: rgb(var(--primary-500));
                        background-color: rgb(var(--primary-50));
                        margin: 1.5rem 0;
                        padding: 1rem;
                        border-radius: 0.5rem;
                    }

                    .filament-documentation-modal .prose code {
                        background-color: rgb(var(--gray-100));
                        padding: 0.2rem 0.4rem;
                        border-radius: 0.25rem;
                        font-size: 0.875em;
                    }

                    /* Dark mode */
                    .dark .filament-documentation-modal .prose h1 {
                        color: rgb(var(--primary-400));
                        border-bottom-color: rgb(var(--gray-700));
                    }

                    .dark .filament-documentation-modal .prose h2 {
                        color: rgb(var(--gray-100));
                    }

                    .dark .filament-documentation-modal .prose h3 {
                        color: rgb(var(--gray-200));
                    }

                    .dark .filament-documentation-modal .prose blockquote {
                        background-color: rgb(var(--primary-950));
                        border-left-color: rgb(var(--primary-500));
                    }

                    .dark .filament-documentation-modal .prose code {
                        background-color: rgb(var(--gray-800));
                    }
                </style>

                {!! $documentacionHtml !!}
            </div>
        </div>
    </div>
</div>

<script>
    // Generar índice de contenido
    document.addEventListener('DOMContentLoaded', () => {
        const toc = document.getElementById('toc');
        const headings = document.querySelectorAll('.prose h2, .prose h3');

        if (toc && headings.length) {
            const ul = document.createElement('ul');
            ul.className = 'space-y-2 list-none pl-0';

            headings.forEach((heading, index) => {
                const id = `heading-${index}`;
                heading.id = id;

                const li = document.createElement('li');
                const a = document.createElement('a');

                a.href = `#${id}`;
                a.textContent = heading.textContent;

                if (heading.tagName === 'H2') {
                    a.className = 'block text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-primary-500 dark:hover:text-primary-400';
                } else {
                    a.className = 'block text-sm text-gray-600 dark:text-gray-400 hover:text-primary-500 dark:hover:text-primary-400 pl-4';
                }

                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    heading.scrollIntoView({ behavior: 'smooth' });
                });

                li.appendChild(a);
                ul.appendChild(li);
            });

            toc.appendChild(ul);
        }
    });
</script>
