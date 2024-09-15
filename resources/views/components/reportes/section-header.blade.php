@props(['level', 'type' => 'default'])

@php
$classes = [
    'default' => 'font-bold bg-slate-700 text-white',
    'section' => 'font-bold bg-blue-700 text-white',
    'caracter' => 'font-bold bg-green-700 text-white mt-2 ml-6',
    'uacad' => 'font-bold bg-zinc-700 text-black mt-2 ml-2',
    'fuente' => 'font-bold bg-green-700 text-white',
    'funcion' => 'font-bold bg-red-700 text-white',
    'banco' => 'font-bold bg-purple-700 text-white',
    'generales' => 'mt-20 font-bold bg-gray-700 text-white',
];

$rowClass = $classes[$type] ?? $classes['default'];
@endphp

<h{{ $level }} class="
    text-{{ 6 - $level }}xl
    font-{{ 800 - $level * 100 }}
    bg-slate-700
    text-gray-{{ 700 - $level * 100 }}
    p-1
    {{ $level == 1 ? 'font-bold' : '' }}
    {{ $level == 6 ? 'mt-4' : 'm-0' }}
    border-2 border-gray-950
    {{ $rowClass }}
">
    {{ $slot }}
</h{{ $level }}>

