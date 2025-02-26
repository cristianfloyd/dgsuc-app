<div class="filament-documentation-modal">
    <div class="flex">
        <!-- Índice de contenidos -->
        <div class="w-1/5 p-4 hidden md:block">
            <div class="sticky top-4 rounded-lg bg-gray-50 dark:bg-gray-900 p-4 shadow">
                <h2 class="text-lg font-bold mb-2">Contenido</h2>
                <div id="toc" class="text-sm"></div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="w-full md:w-4/5 p-4">
            <div class="flex justify-between items-center mb-4 pb-2 border-b">
                <div>
                    <input
                        type="text"
                        placeholder="Buscar en la documentación..."
                        class="px-3 py-2 border rounded-lg w-64"
                        id="docSearchInput"
                    >
                </div>
                <div>
                    <button
                        onclick="window.print()"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg border border-gray-300 bg-white hover:bg-gray-50"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimir
                    </button>
                </div>
            </div>
            <div class="prose dark:prose-invert prose-headings:border-b prose-headings:border-gray-200 dark:prose-headings:border-gray-700 prose-headings:pb-2 prose-headings:mb-4 max-w-none">
                {!! $documentacionHtml !!}
            </div>
        </div>
    </div>
</div>

<script>
    // Crear índice de contenido dinámicamente
    document.addEventListener('DOMContentLoaded', () => {
        const tocContainer = document.getElementById('toc');
        const headings = document.querySelectorAll('.prose h2, .prose h3');

        if (tocContainer && headings.length) {
            const list = document.createElement('ul');
            list.className = 'space-y-1';

            headings.forEach((heading, index) => {
                // Añadir ID a cada encabezado si no tiene
                if (!heading.id) {
                    heading.id = 'heading-' + index;
                }

                const listItem = document.createElement('li');
                const link = document.createElement('a');

                link.textContent = heading.textContent;
                link.href = '#' + heading.id;
                link.className = heading.tagName === 'H2'
                    ? 'block text-gray-700 dark:text-gray-300 hover:text-primary-500 dark:hover:text-primary-400'
                    : 'block pl-4 text-gray-600 dark:text-gray-400 hover:text-primary-500 dark:hover:text-primary-400';

                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    heading.scrollIntoView({behavior: 'smooth'});
                });

                listItem.appendChild(link);
                list.appendChild(listItem);
            });

            tocContainer.appendChild(list);
        }
    });
</script>
