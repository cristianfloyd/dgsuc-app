<div>
    <div class="container mx-auto mt-10 bg-gray-900 text-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6">Afip Mapuche Mi Simplificación</h2>
        <table class="table-auto w-full">
            <thead>
                <tr>
                    @foreach ($this->headers as $header)
                        <th class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $header)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr class="bg-gray-800">
                    @foreach ($this->headers as $header)
                        <td class="border px-4 py-2">XX</td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>

