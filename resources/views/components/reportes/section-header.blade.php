<h{{ $level }} class="
    text-{{ 6 - $level }}xl
    font-{{ 800 - $level * 100 }}
    mb-2
    bg-slate-800
    text-gray-{{ 700 - $level * 100 }}
    p-2
    {{ $level == 1 ? 'font-bold' : '' }}
    {{ $level == 1 ? 'border-b-2' : '' }}
    ml-{{ $level }}
    boder border-gray-950
">
    {{ $slot }}
</h{{ $level }}>

