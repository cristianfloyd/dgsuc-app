@props(['totals', 'type' => 'default'])

@php
$classes = [
    'default' => 'font-bold bg-slate-700 text-white',
    'section' => 'font-bold bg-blue-700 text-white',
    'fuente' => 'font-bold bg-green-700 text-white mt-3 ml-0',
    'uacad' => 'font-bold bg-green-600 text-black mt-2 ml-2',
    'funcion' => 'font-bold bg-indigo-700 text-white mt-4',
    'banco' => 'font-bold bg-purple-700 text-white mt-4',
        'generales' => 'mt-20 font-bold bg-gray-700 text-white',
        'subtotal-funcion' => 'font-bold bg-slate-600 text-white',
        'subtotal-forma-pago' => 'font-bold bg-gray-700 text-white',
        'total-general' => 'font-bold bg-gray-700 text-white',

];

$rowClass = $classes[$type] ?? $classes['default'];
@endphp

<tr class="{{$rowClass}}">
    @if ($type == 'subtotal-funcion')
    <td class="border border-gray-600 text-right pr-2 py-1">...</td>
    <td class="border border-gray-600 text-right pr-2 py-1">Sub Total</td>
    @elseif ($type == 'subtotal-forma-pago')
        <td class="border border-gray-600 text-right pr-2 py-1">Sub Totales</td>
        <td class="border border-gray-600 text-right pr-2 py-1">...</td>
    @elseif ($type == 'total-general')

    @else
        <td class="border border-gray-600 text-right pr-2 py-1">Sub Totales</td>
    @endif
    @foreach($totals as $total)
        <td class="border border-gray-600 text-right pr-2 py-1">{{ money($total) }}</td>
    @endforeach
</tr>
