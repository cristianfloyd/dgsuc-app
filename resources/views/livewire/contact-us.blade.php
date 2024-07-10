<div>
    <div class="flex items-center justify-center p-12">
        <div class="mx-auto w-full max-w-[550px]">
            @if (session()->has('success'))
                <div>
                    <div class="max-w-xs bg-green-500 text-sm text-white rounded-md shadow-lg mb-3 ml-3" role="alert">
                        <div class="flex p-4">
                            {{ session('success') }}
                            <div class="ml-auto">
                                <button wire:click="closeAlert()" type="button"
                                    class="inline-flex flex-shrink-0 justify-center items-center h-4 w-4 rounded-md text-white/[.5] hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-800 focus:ring-green-500 transition-all text-sm dark:focus:ring-offset-green-500 dark:focus:ring-green-700">
                                    <span class="sr-only">Close</span>
                                    <svg class="w-3.5 h-3.5" width="16" height="16" viewBox="0 0 16 16"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M0.92524 0.687069C1.126 0.486219 1.39823 0.373377 1.68209 0.373377C1.96597 0.373377 2.2382 0.486219 2.43894 0.687069L8.10514 6.35813L13.7714 0.687069C13.8701 0.584748 13.9882 0.503105 14.1188 0.446962C14.2494 0.39082 14.3899 0.361248 14.5321 0.360026C14.6742 0.358783 14.8151 0.38589 14.9468 0.439762C15.0782 0.493633 15.1977 0.573197 15.2983 0.673783C15.3987 0.774389 15.4784 0.894026 15.5321 1.02568C15.5859 1.15736 15.6131 1.29845 15.6118 1.44071C15.6105 1.58297 15.5809 1.72357 15.5248 1.85428C15.4688 1.98499 15.3872 2.10324 15.2851 2.20206L9.61883 7.87312L15.2851 13.5441C15.4801 13.7462 15.588 14.0168 15.5854 14.2977C15.5831 14.5787 15.4705 14.8474 15.272 15.046C15.0735 15.2449 14.805 15.3574 14.5244 15.3599C14.2437 15.3623 13.9733 15.2543 13.7714 15.0591L8.10514 9.38812L2.43894 15.0591C2.23704 15.2543 1.96663 15.3623 1.68594 15.3599C1.40526 15.3574 1.13677 15.2449 0.938279 15.046C0.739807 14.8474 0.627232 14.5787 0.624791 14.2977C0.62235 14.0168 0.730236 13.7462 0.92524 13.5441L6.59144 7.87312L0.92524 2.20206C0.724562 2.00115 0.611816 1.72867 0.611816 1.44457C0.611816 1.16047 0.724562 0.887983 0.92524 0.687069Z"
                                            fill="currentColor" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <h2 class="text-3xl font-semibold text-gray-400 text-center mb-8">
                {{ __('Contact Us') }}
            </h2>
            <form method="POST">
                <div class="mb-5">
                    <label for="name" class="mb-3 block text-base font-medium text-gray-400">
                        {{ __('Name') }}
                    </label>
                    <input wire:model="form.name" type="text" name="name" id="name"
                        placeholder="{{ __('Name') }}"
                        class="w-full rounded-md border border-[#e0e0e0] bg-white py-3 px-6 text-base font-medium text-[#6B7280] outline-none focus:border-[#6A64F1] focus:shadow-md" />
                    @error('form.name')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mb-5">
                    <label for="email" class="mb-3 block text-base font-medium text-gray-400">
                        {{__('Email')}}
                    </label>
                    <input wire:model="form.email" type="email" name="email" id="email"
                        placeholder="example@domain.com"
                        class="w-full rounded-md border border-[#e0e0e0] bg-white py-3 px-6 text-base font-medium text-[#6B7280] outline-none focus:border-[#6A64F1] focus:shadow-md" />
                    @error('form.email')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mb-5">
                    <label for="phone" class="mb-3 block text-base font-medium text-gray-400">
                        {{ __('Phone') }}
                    </label>
                    <input wire:model="form.phoneNumber" type="text" name="phone" id="phone"
                        placeholder="{{__('Phone Number')}}"
                        class="w-full rounded-md border border-[#e0e0e0] bg-white py-3 px-6 text-base font-medium text-[#6B7280] outline-none focus:border-[#6A64F1] focus:shadow-md" />
                </div>
                <div class="mb-5">
                    <label for="message" class="mb-3 block text-base font-medium text-gray-400">
                        {{ __('Message') }}
                    </label>
                    <textarea wire:model="form.message" rows="4" name="message" id="message" placeholder="{{__('Type your message')}}"
                        class="w-full resize-none rounded-md border border-[#e0e0e0] bg-white py-3 px-6 text-base font-medium text-[#6B7280] outline-none focus:border-[#6A64F1] focus:shadow-md"></textarea>
                    @error('form.message')
                        <span class="text-red-500">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mb-5">
                    <label for="images" class="mb-3 block text-base font-medium text-gray-400">
                        {{ __('Upload File') }}
                    </label>
                    <input multiple
                        wire:model="form.images"
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                        aria-describedby="user_avatar_help" id="images" type="file"
                        accept="image/png,image/jpeg,image/jpg">
                </div>
                <div wire:loading wire:target="images">
                    <span class="text-green-500">{{__('Loading images...')}}</span>
                </div>
                <div wire:loading.delay.long>
                    <span class="text-green-500">{{ __('Sending') }}...</span>
                </div>
                @if ($form->images)
                    @foreach ($form->images as $image )
                        <img src="{{ $image->temporaryUrl() }}" class="mt-2 w-20 h-20 rounded-lg block" alt="avatar" />
                    @endforeach
                @endif
                <div>
                    <button wire:click.prevent="submitForm" type="submit"
                        class="hover:shadow-form rounded-md bg-[#6A64F1] py-3 px-8 text-base font-semibold text-white outline-none">
                        {{ __('Submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
