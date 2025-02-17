<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    @foreach($docs->groupBy('section') as $section => $documents)
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">
                                {{ ucfirst($section) }}
                            </h2>
                            <div class="space-y-2">
                                @foreach($documents as $doc)
                                    <div>
                                        <a href="{{ route('documentation.show', $doc->slug) }}"
                                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            {{ $doc->title }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
