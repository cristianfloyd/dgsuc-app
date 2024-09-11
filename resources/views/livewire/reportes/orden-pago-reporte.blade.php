<div class="container mx-auto w-7/12">
    <div class="bg-gray p-6 rounded-lg shadow-lg">
        @foreach($reportData as $banco => $porBanco)
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4 text-center bg-gray-800 py-2 rounded">Banco: {{ $banco }}</h2>
                @foreach($porBanco as $codn_funci => $porFunci)
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold mb-3 text-center bg-gray-700 py-1 rounded">Función: {{ $codn_funci }}</h3>
                        @foreach($porFunci as $codn_fuent => $porFuente)
                            <div class="mb-4">
                                <h4 class="text-lg font-medium mb-2 text-center bg-gray-600 py-1 rounded">Fuente: {{ $codn_fuent }}</h4>
                                @foreach($porFuente as $codc_uacad => $data)
                                    <div class="mb-4">
                                        <h5 class="text-md font-medium mb-2 text-center">Unidad Académica: {{ $codc_uacad }}</h5>
                                        <div class="overflow-x-auto">
                                            <table class="w-full bg-gray-550 shadow-md rounded-lg overflow-hidden">
                                                <thead class="bg-gray-500 text-gray-100">
                                                    <tr>
                                                        <th class="py-3 px-4 text-center">Programa</th>
                                                        <th class="py-3 px-4 text-center">Sueldo</th>
                                                        <th class="py-3 px-4 text-center">Estipendio</th>
                                                        <th class="py-3 px-4 text-center">Productividad</th>
                                                        <th class="py-3 px-4 text-center">Méd. Resid.</th>
                                                        <th class="py-3 px-4 text-center">Sal. Fam.</th>
                                                        <th class="py-3 px-4 text-center">Hs. Extras</th>
                                                        <th class="py-3 px-4 text-center">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($data as $row)
                                                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                                                            <td class="py-3 px-4 text-center">{{ $row->codn_progr }}</td>
                                                            <td class="py-3 px-4 text-center">{{ $row->remunerativo }}</td>
                                                            <td class="py-3 px-4 text-center">{{ $row->no_remunerativo }}</td>
                                                            <td class="py-3 px-4 text-center">{{ $row->productividad ?? 'N/A' }}</td>
                                                            <td class="py-3 px-4 text-center">{{ $row->med_resid ?? 'N/A' }}</td>
                                                            <td class="py-3 px-4 text-center">{{ $row->sal_fam ?? 'N/A' }}</td>
                                                            <td class="py-3 px-4 text-center">{{ $row->hs_extras }}</td>
                                                            <td class="py-3 px-4 text-center">{{ $row->total ?? 1 }}</td>
                                                        </tr>
                                                    @endforeach
                                                    <tr class="bg-gray-550 font-semibold">
                                                        <td class="py-3 px-4 text-center">Total</td>
                                                        <td class="py-3 px-4 text-center">{{ $data->sum('remunerativo') }}</td>
                                                        <td class="py-3 px-4 text-center">{{ $data->sum('no_remunerativo') }}</td>
                                                        <td class="py-3 px-4 text-center">{{ $data->sum('productividad') ?? 'N/A' }}</td>
                                                        <td class="py-3 px-4 text-center">{{ $data->sum('med_resid') ?? 'N/A' }}</td>
                                                        <td class="py-3 px-4 text-center">{{ $data->sum('sal_fam') ?? 'N/A' }}</td>
                                                        <td class="py-3 px-4 text-center">{{ $data->sum('hs_extras') }}</td>
                                                        <td class="py-3 px-4 text-center">{{ $data->sum('total') }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
