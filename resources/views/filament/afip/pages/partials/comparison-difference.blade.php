<div class="flex justify-between items-center pt-2 border-t">
    <span class="text-gray-600">Diferencia:</span>
    <span class="font-medium {{ $difference > 0 ? 'text-danger-600' : 'text-success-600' }}">
        $ {{ number_format($difference, 2, ',', '.') }}
    </span>
</div>
