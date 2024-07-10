<div class="container content py-6 mx-auto">
    <div class="mx-auto">
        <div id="create-form" class="bg-gray-400 dark:bg-gray-800">
            <div class="flex ">
                <h2 class="font-semibold text-lg text-gray-500 mb-5">Create New Todo</h2>
            </div>
            <div >
                <form>
                    <div class="mb-6">
                        <label for="name"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">*
                            Todo </label>

                        <input wire:model="name" type="text" id="name" placeholder="Todo.."
                            class="bg-gray-100  text-gray-900 text-sm rounded block w-full p-2.5">

                        @error('create')
                            <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                        @enderror

                    </div>
                    <button wire:click.prevent="create"
                        class="px-4 py-2 bg-blue-500 text-white font-semibold rounded hover:bg-blue-600">
                        Create
                    </button>
                    @if (session('success'))
                        <span class="text-green-500 text-xs">{{ session('success') }} </span>
                    @endif
                    @if (session('error'))
                        <span class="text-red-500 text-xs">{{ session('error') }} </span>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
