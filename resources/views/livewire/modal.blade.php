<div>

    <div class="max-w-2xl mx-auto mb-4">
        <x-livewire.modal name="test" title="{{__('Create New Account')}}">
            @slot('content')
                <div>
                    <livewire:register-form />
                </div>
            @endslot
        </x-livewire.modal>
        <x-livewire.modal name="modal2" title="Modal 2">
            @slot('content')
                <div>
                    <p class="p-5">Modal 2 </p>
                </div>
            @endslot
        </x-livewire.modal>
        <button
            x-data
            x-on:click = "$dispatch('open-modal',{ name : 'test' })"
            class="px-3 py-1 bg-teal-500 text-white rounded">Open Modal 1</button>
        <button
            x-data
            x-on:click = "$dispatch('open-modal',{ name : 'modal2' })"
            class="px-3 py-1 bg-teal-500 text-white rounded">Open Modal 2</button>
    </div>
    <livewire:user-list />
</div>
