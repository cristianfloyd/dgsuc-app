<div class="bg-gray-600">

    <div class="w-3/4">
        <div>
            @if (session('success'))
                <span class="text-green-500 text-xs">{{ session('success') }} </span>
            @endif
            @if (session('error'))
                <span class="text-red-500 text-xs">{{ session('error') }} </span>
            @endif
            @if (Session('user-created'))
                <span class="text-green-500 text-xs">{{ Session('user-created') }} </span>
            @endif



        </div>
        <form class="max-w-sm mx-auto pb-3 pt-3">
            <div class="mb-5">
                <label for="name"
                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Name') }}</label>
                <input type="text" id="name" wire:model="name"
                    class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"
                    placeholder="Name" />
                @error('name')
                    <span class="text-red-800 text-xs">{{ $message }} </span>
                @enderror
            </div>
            <div class="mb-5">
                <label for="email"
                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Email') }}</label>
                <input type="email" id="email" wire:model="email"
                    class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"
                    placeholder="name@email.com" />
                @error('email')
                    <span class="text-red-800 text-xs">{{ $message }} </span>
                @enderror
            </div>
            <div class="mb-5">
                <label for="password"
                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Password') }}</label>
                <input type="password" id="password" wire:model="password"
                    class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light" />
                @error('password')
                    <span class="text-red-800 text-xs">{{ $message }} </span>
                @enderror
            </div>
            {{-- <div class="mb-5">
                <label for="repeat-password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{__('Confirm Password')}}</label>
                <input type="password" id="repeat-password"
                    class="shadow-sm bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 dark:shadow-sm-light"
                    />
            </div> --}}
            <div class="flex items-start mb-5">
                <div class="flex items-center h-5">
                    <input id="terms" type="checkbox" value="" wire:model="terms"
                        class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-blue-300 dark:bg-gray-700 dark:border-gray-600 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800" />
                    @error('terms')
                        <span class="text-red-800 text-xs">{{ $message }} </span>
                    @enderror
                </div>
                <label for="terms"
                    class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">{{ __('I agree with the') }} <a
                        href="#"
                        class="text-blue-600 hover:underline dark:text-blue-500">{{ __('terms and conditions') }}</a></label>
            </div>
            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                for="user_avatar">{{ __('Upload File') }}</label>
            <input wire:model="image"
                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                aria-describedby="user_avatar_help" id="image" type="file"
                accept="image/png,image/jpeg,image/jpg">
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="user_avatar_help">
                {{ __('A profile picture is useful to confirm your are logged into your account') }}
            </div>
            @error('image')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
            <div wire:loading wire:target="image">
                <span class="text-green-500">Loading image...</span>
            </div>
            <div wire:loading.delay.long>
                <span class="text-green-500">{{ __('Sending') }}...</span>
            </div>
            @if ($image)
                <img src="{{ $image->temporaryUrl() }}" class="mt-2 w-20 h-20 rounded-lg block" alt="avatar" />
            @endif
            <button type="submit" wire:click.prevent="createUser" wire:loading.remove
                class="mt-4 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                {{ __('Create Account') }}
            </button>
            <button type="button" @click="$dispatch('user-created')"
                class="mt-4 text-white bg-teal-700 hover:bg-teal-800 focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-teal-600 dark:hover:bg-teal-700 dark:focus:ring-teal-800">
                {{ __('Reload List') }}
            </button>
        </form>
    </div>
</div>
