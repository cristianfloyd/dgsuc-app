<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg relative">
            
            <!-- Dark mode toggle -->
            <div class="absolute top-4 right-4">
                <button
                    @click="darkMode = !darkMode"
                    class="p-2 rounded-full transition-colors duration-300 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-400"
                    :title="darkMode ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'">
                    <svg x-show="!darkMode" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                    <svg x-show="darkMode" class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </button>
            </div>

            <div class="text-center mb-8">
                <img src="{{ asset('images/logofooter.png') }}" alt="Logo UBA" class="mx-auto h-16 w-auto mb-4" />
                <h1 class="mt-4 text-2xl font-semibold text-gray-900 dark:text-white transition-colors duration-300">Iniciar Sesión</h1>
            </div>

            <x-validation-errors class="mb-4" />

            @session('status')
                <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400 transition-colors duration-300">
                    {{ $value }}
                </div>
            @endsession

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div>
                    <x-label for="email" value="{{ __('Username/Email') }}" class="text-gray-700 dark:text-gray-300 transition-colors duration-300" />
                    <x-input id="email" class="block mt-1 w-full dark:bg-gray-700 dark:text-white dark:border-gray-600 transition-colors duration-300" type="text" name="identity" :value="old('email')" required autofocus autocomplete="username/email" />
                </div>

                <div class="mt-4">
                    <x-label for="password" value="{{ __('Password') }}" class="text-gray-700 dark:text-gray-300 transition-colors duration-300" />
                    <x-input id="password" class="block mt-1 w-full dark:bg-gray-700 dark:text-white dark:border-gray-600 transition-colors duration-300" type="password" name="password" required autocomplete="current-password" />
                </div>

                <div class="flex items-center justify-between mt-4">
                    <label for="remember_me" class="flex items-center">
                        <x-checkbox id="remember_me" name="remember" class="dark:bg-gray-700 transition-colors duration-300" />
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400 transition-colors duration-300">{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-colors duration-300" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                <div class="flex items-center justify-end mt-4">
                    <x-button class="w-full justify-center">
                        {{ __('Log in') }}
                    </x-button>
                </div>
            </form>

            <div class="text-center mt-6 space-y-3">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 transition-colors duration-300">
                            o
                        </span>
                    </div>
                </div>

                <a href="{{ route('auth.office365') }}" class="w-full justify-center inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.5 12c0-6.351-5.149-11.5-11.5-11.5s-11.5 5.149-11.5 11.5 5.149 11.5 11.5 11.5 11.5-5.149 11.5-11.5zm-6.5 0c0 2.761-2.239 5-5 5s-5-2.239-5-5 2.239-5 5-5 5 2.239 5 5z"/>
                    </svg>
                    {{ __('UBA account') }}
                </a>

                <a href="/toba" class="w-full justify-center inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                    Sistema Mapuche
                </a>
            </div>

            <div class="text-center mt-6">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Sistema de Gestión Universitaria - Universidad de Buenos Aires
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>
