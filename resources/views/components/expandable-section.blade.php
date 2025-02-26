<div x-data="{ open: {{ $expanded ? 'true' : 'false' }} }" class="mb-4 rounded-lg border border-gray-200 dark:border-gray-700">
    <button
        @click="open = !open"
        class="flex items-center justify-between w-full p-4 text-left bg-gray-50 dark:bg-gray-800 rounded-t-lg"
    >
        <h3 class="text-lg font-medium">{{ $title }}</h3>
        <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
        <svg x-show="open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
        </svg>
    </button>
    <div x-show="open" x-transition class="p-4 bg-white dark:bg-gray-900 rounded-b-lg">
        {{ $slot }}
    </div>
</div>
