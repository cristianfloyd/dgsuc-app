@php
    $brandName = config('app.name');
@endphp

<x-filament-panels::page.simple>
    <x-slot name="subheading">
        Accede con tus credenciales del sistema Mapuche
    </x-slot>

    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.login.form.before') }}

    <x-filament-panels::form wire:submit="authenticate">
        <x-filament::input.wrapper>
            <x-filament::input
                type="text"
                wire:model="data.usuario"
                placeholder="Usuario"
                autocomplete="username"
                required
                autofocus
            />
        </x-filament::input.wrapper>
        
        @error('data.usuario')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror

        <x-filament::input.wrapper class="mt-4">
            <x-filament::input
                type="password"
                wire:model="data.clave"
                placeholder="Contraseña"
                autocomplete="current-password"
                required
            />
        </x-filament::input.wrapper>

        <x-filament::button
            type="submit"
            class="w-full mt-6"
            size="lg"
        >
            Iniciar Sesión
        </x-filament::button>
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook('panels::auth.login.form.after') }}

    <div class="text-center mt-6">
        <x-filament::link
            href="{{ route('login') }}"
            size="sm"
            color="gray"
        >
            ¿Prefieres usar tu cuenta Laravel?
        </x-filament::link>
    </div>

    <div class="text-center mt-4">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Sistema de Gestión Universitaria - Universidad de Buenos Aires
        </p>
    </div>
</x-filament-panels::page.simple>