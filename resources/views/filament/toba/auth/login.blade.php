<x-filament-panels::page.simple>
    <x-slot name="subheading">
        {{ $this->getSubheading() }}
    </x-slot>

    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.login.form.before') }}

    <x-filament-panels::form wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.login.form.after') }}

    <div class="text-center mt-6 space-y-3">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400">
                    o
                </span>
            </div>
        </div>

        <x-filament::link
            href="{{ route('login') }}"
            size="sm"
            color="gray"
            class="block"
        >
            Iniciar sesión con usuario DGSUC
        </x-filament::link>
    </div>

    <div class="text-center mt-6">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            Sistema de Gestión Universitaria - Universidad de Buenos Aires
        </p>
    </div>
</x-filament-panels::page.simple>