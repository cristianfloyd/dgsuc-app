<div class="container mx-auto w-7/12 bg-white">
    <div class="bg-gray p-6 rounded-lg shadow-lg">
        @foreach($reportData as $banco => $porBanco)
            <div class="mb-8">
                <h2 class="text-2xl text-white font-bold pl-3 mb-0 text-left bg-slate-700  py-1 rounded">
                    FORMA PAGO: {{  $banco == '0' ? 'BANCO' : 'EFECTIVO' }}</h2>
                @foreach($porBanco as $codn_funci => $porFunci)
                    <div class="mb-6">
                        <h3 class="text-xl text-white font-semibold mb-0 text-center bg-slate-600 py-1 rounded">
                            FORMA DE PAGO {{  $banco == '0' ? 'BANCO' : 'EFECTIVO' }} | Función: {{ $codn_funci }}
                        </h3>
                        @foreach($porFunci as $codn_fuent => $porFuente)
                            <div class="mb-4">
                                <h4 class="text-lg text-white font-medium mb-0 text-center bg-slate-500 py-1 rounded">
                                    Fuente de financiamiento: {{ $codn_fuent }}</h4>
                                @foreach($porFuente as $codc_uacad => $data)
                                    <div class="mb-4">
                                        <h5
                                            class="text-md text-left font-medium mb-2 my-1 border-solid border-2 border-slate-500 py-1 rounded pl-2 text-gray-900">
                                            Unidad Académica: {{ $codc_uacad }}
                                        </h5>
                                        @foreach ($porCarac as $codn_progr => $data)
                                            <div class="mb-1">
                                                {{-- <h6 class="textarea-xs text-left font-thin my-1 border-solid border-1 border-slate-500 py-0 rounded pl-2 text-gray-900">
                                                    Personal {{ $codn_progr  }}
                                                </h6> --}}
                                                <table border="1">
                                                    <thead>
                                                        <tr>
                                                            <th colspan="8">Dependencia {{ $codn_progr }}</th>
                                                        </tr>
                                                        <tr>
                                                            <th>Programa</th>
                                                            <th>Sueldo</th>
                                                            <th>Estipendio</th>
                                                            <th>Productividad</th>
                                                            <th>Méd. Resid.</th>
                                                            <th>Sal. Fam.</th>
                                                            <th>Hs. Extras</th>
                                                            <th>Total</th>
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
