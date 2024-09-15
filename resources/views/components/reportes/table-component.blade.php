<div class="overflow-x-auto ml-6">
    <table class="min-w-full bg-white border-collapse border border-gray-600 mb-2">
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th class="py-2 px-4 border-b-2 border-gray-600 bg-gray-400 text-gray-800">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
