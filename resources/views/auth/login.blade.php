<x-guest-layout>
    <div x-data="{ darkMode: true }"
         x-init="darkMode = localStorage.getItem('darkMode') === 'true'"
         @dark-mode-changed.window="darkMode = $event.detail"
         class="transition-all duration-300 ease-in-out"
         :class="{ 'dark': darkMode }">

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300 ease-in-out py-6 flex flex-col justify-center sm:py-12">
            <div class="relative py-3 sm:max-w-xl sm:mx-auto">
                <!-- Fondo con gradiente y transición -->
                <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg transform transition-all duration-300 ease-in-out -skew-y-6 sm:skew-y-0 sm:-rotate-6 sm:rounded-3xl"></div>

                <!-- Contenedor principal con transición -->
                <div class="relative px-4 py-10 bg-white dark:bg-gray-800 transition-colors duration-300 ease-in-out shadow-lg sm:rounded-3xl sm:p-20">

                    <!-- Botón de tema con transición -->
                    <div class="absolute top-4 right-4">
                        <button
                            @click="
                                darkMode = !darkMode;
                                localStorage.setItem('darkMode', darkMode);
                                $dispatch('dark-mode-changed', darkMode)
                            "
                            class="p-2 rounded-full transition-colors duration-300 ease-in-out hover:bg-gray-200 dark:hover:bg-gray-700">
                            <template x-if="darkMode">
                                <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </template>
                            <template x-if="!darkMode">
                                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                                </svg>
                            </template>
                        </button>
                    </div>

                    <!-- Contenido del formulario con transiciones -->
                    <div class="max-w-md mx-auto">
                        <div class="divide-y divide-gray-200 dark:divide-gray-700 transition-colors duration-300">
                            <div class="py-8 text-base leading-6 space-y-4 text-gray-700 dark:text-gray-300 sm:text-lg sm:leading-7">
                                <div class="text-center mb-8">
                                    <x-authentication-card-logo class="mx-auto h-12 w-auto" />
                                    <h1 class="mt-6 text-3xl font-bold text-gray-900 dark:text-white transition-colors duration-300 ease-in-out">Bienvenido</h1>
                                </div>

                                <x-validation-errors class="mb-4" />

                                @session('status')
                                    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400 transition-colors duration-300 ease-in-out">
                                        {{ $value }}
                                    </div>
                                @endsession

                                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                                    @csrf
                                    <div>
                                        <x-label for="email" value="{{ __('Username/Email') }}" class="text-gray-700 dark:text-gray-300 transition-colors duration-300 ease-in-out" />
                                        <x-input id="email" class="block mt-1 w-full dark:bg-gray-700 dark:text-white dark:border-gray-600 transition-colors duration-300 ease-in-out" type="text" name="identity" :value="old('email')" required autofocus autocomplete="username/email" />
                                    </div>

                                    <div class="mt-4">
                                        <x-label for="password" value="{{ __('Password') }}" class="text-gray-700 dark:text-gray-300 transition-colors duration-300 ease-in-out" />
                                        <x-input id="password" class="block mt-1 w-full dark:bg-gray-700 dark:text-white dark:border-gray-600 transition-colors duration-300 ease-in-out" type="password" name="password" required autocomplete="current-password" />
                                    </div>

                                    <div class="flex items-center justify-between mt-4">
                                        <label class="flex items-center">
                                            <x-checkbox id="remember_me" name="remember" class="dark:bg-gray-700 transition-colors duration-300 ease-in-out" />
                                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400 transition-colors duration-300 ease-in-out">{{ __('Remember me') }}</span>
                                        </label>

                                        @if (Route::has('password.request'))
                                            <a class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors duration-300 ease-in-out" href="{{ route('password.request') }}">
                                                {{ __('Forgot your password?') }}
                                            </a>
                                        @endif
                                    </div>

                                    <div class="mt-6">
                                        <x-button class="w-full justify-center transition-colors duration-300 ease-in-out">
                                            {{ __('Log in') }}
                                        </x-button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Estilos adicionales para transiciones suaves */
        * {
            transition-property: background-color, border-color, color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }
    </style>
</x-guest-layout>
