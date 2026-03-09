<footer
    role="contentinfo"
    class="fi-footer border-t border-gray-200/80 dark:border-white/5 bg-gray-50/80 dark:bg-white/[0.02]"
>
    <div class="mx-auto flex max-w-[1600px] flex-wrap items-center justify-between gap-x-4 gap-y-1.5 px-4 py-1.5 text-[11px] leading-tight text-gray-500 dark:text-gray-400">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
            <span title="Conexión BD">{{ $connectionName ?: 'Default' }}</span>
            <span class="select-none text-gray-300 dark:text-gray-600" aria-hidden="true">·</span>
            <span title="Laravel">Laravel {{ $laravelVersion }}</span>
            <span class="select-none text-gray-300 dark:text-gray-600" aria-hidden="true">·</span>
            <span title="Filament">Filament {{ $filamentVersion }}</span>
        </div>
        <div class="flex items-center gap-x-2 font-medium text-gray-700 dark:text-gray-300">
            <span>{{ $appName }}</span>
            <span class="rounded px-1.5 py-0.5 bg-primary-500/10 text-primary-600 dark:text-primary-400">v{{ $appVersion }}</span>
        </div>
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
            @auth
                <span class="max-w-[7rem] truncate" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</span>
                <span class="select-none text-gray-300 dark:text-gray-600" aria-hidden="true">·</span>
            @endauth
            <span class="font-mono tabular-nums" wire:poll.10s="updateTime" title="Hora servidor">{{ $currentTime }}</span>
        </div>
    </div>
</footer>
