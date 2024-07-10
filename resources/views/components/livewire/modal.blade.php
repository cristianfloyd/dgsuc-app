@props(['title', 'content', 'name'])
<div class="fixed z-50 inset-0"
    x-data = "{ show: false }"
    x-show="show"
    x-on:open-modal.window ="console.log($event.detail); show = ($event.detail.name === '{{$name}}')"
    x-on:close-modal.window ="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-transition.duration
    style="display: none;"
    >
    {{-- Gray Background --}}
    <div
        x-on:click="show = false"
        class="fixed inset-0 bg-gray-300 opacity-40">
    </div>
    {{-- Modal Body --}}
    <div class="bg-gray-600 text-gray-200 rounded m-auto fixed inset-0 max-w-2xl" style="max-height: 500px">
        <div>
            <button
            x-on:click="$dispatch('close-modal')"
            class="float-right px-3 py-1 bg-red-500 text-white rounded">{{__('Close')}}</button>
            @if (isset($title))
                <h2 class="text-lg text-center font-bold p-3 bg-gray-700">{{ $title }}</h2>
            @endif
        </div>
        <div>
            @if (isset($content))
                {{ $content }}
            @endif
        </div>
    </div>
</div>
