@props(['headers', 'type' => 'default'])

@php
$classes = [
    'default' => 'font-bold bg-slate-700 text-white mt-2 ml-6',
    'section' => 'font-bold bg-blue-700 text-white',
    'caracter' => 'font-bold bg-green-700 text-white mt-2 ml-6',
        'uacad' => 'font-bold bg-zinc-700 text-black mt-2 ml-2',
    'fuente' => 'font-bold bg-green-700 text-white ml-0',
    'funcion' => 'font-bold bg-red-700 text-white',
    'banco' => 'font-bold bg-purple-700 text-white',
    'generales' => 'mb-20 font-bold bg-gray-700 text-white',
        'subtotal-funcion' => 'font-bold bg-red-700 text-white',
];

$rowClass = $classes[$type] ?? $classes['default'];
@endphp
<div class="{{ $rowClass }}">
    <table class="min-w-full bg-white border-collapse border border-gray-600 mb-2">
        <thead class="py-1 px-2 border-b-2 border-gray-600 bg-gray-400 text-gray-800">
            <tr>
                @foreach($headers as $header)
                    <th >{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
