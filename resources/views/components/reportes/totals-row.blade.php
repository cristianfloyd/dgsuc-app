<tr class="font-bold bg-slate-700 ">
    <td class="border border-gray-600 text-right">Total</td>
    @foreach($totals as $total)
        <td class="border border-gray-600 text-right">{{ money($total) }}</td>
    @endforeach
</tr>
