<div class="container mx-auto w-10/12 bg-white p-6">
    @foreach ($reportData as $banco => $porBanco)

            <x-reportes.section-header :level="2">
                FORMA PAGO: {{ $banco == '1' ? 'BANCO' : 'EFECTIVO' }}
            </x-reportes.section-header>

            @foreach ($porBanco['funciones'] as $funcion => $porFunci)

                    <x-reportes.section-header :level="3">
                        FUNCIÓN: {{ $funcion }}
                    </x-reportes.section-header>
                    @foreach ($porFunci['fuentes'] as $fuente => $porFuente)

                        <x-reportes.section-header :level="4">
                            FUENTE DE FINANCIAMIENTO: {{ $fuente }}
                        </x-reportes.section-header>
                        @foreach ($porFuente['unidades'] as $uacad => $porUacad)

                            <x-reportes.section-header :level="5">
                                UNIDAD ACADÉMICA: {{ $uacad }}
                            </x-reportes.section-header>
                            @foreach ($porUacad['caracteres'] as $caracter => $data)

                                <x-reportes.section-header :level="6">
                                    modalidad: {{ $caracter == 'CONT' ? 'contratado' : 'permanente' }}
                                </x-reportes.section-header>
                                <x-reportes.table-component :headers="['Programa','Sueldo','Estipendio','Productividad','Méd. Resid.','Sal. Fam.','Hs. Extras','Total',]">
                                    @foreach ($data['items'] as  $item)
                                        <tr>
                                            <td class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right">{{ $item->codn_progr }}</td>
                                            <td class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right">{{ money($item->remunerativo) }}</td>
                                            <td class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right">{{ money($item->estipendio) }}</td>
                                            <td class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right">{{ money($item->productividad) }}</td>
                                            <td class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right">{{ money($item->med_resid) }}</td>
                                            <td class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right">{{ money($item->sal_fam) }}</td>
                                            <td class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right">{{ money($item->hs_extras) }}</td>
                                            <td class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right">{{ money($item->total) }}</td>
                                        </tr>
                                    @endforeach
                                    <x-reportes.totals-row :totals="$data['totals']" />
                                </x-reportes.table-component>
                            @endforeach
                            <x-reportes.table-component class="b-slate-500" :headers="['Total: '. $uacad .'' ,'Sueldo', 'Estipendio', 'Productividad', 'Med. Resid.', 'Sal. Fam.', 'Hs. Extras', 'Total']">
                                <x-reportes.totals-row :totals="$porUacad['totalUacad']" />
                            </x-reportes.table-component>

                        @endforeach
                        <x-reportes.section-header :level="4">
                            TOTAL FUENTE DE FINANCIAMIENTO: {{ $fuente }}
                        </x-reportes.section-header>
                        <x-reportes.table-component :headers="['','Remunerativo', 'Estipendio', 'Productividad', 'Med. Resid.', 'Sal. Fam.', 'Hs. Extras', 'Total']">
                            <x-reportes.totals-row :totals="$porFuente['totalFuente']" />
                        </x-reportes.table-component>
                    @endforeach
                    <x-reportes.section-header :level="3">
                        TOTAL FUNCIÓN: {{ $funcion }}
                    </x-reportes.section-header>
                    <x-reportes.table-component :headers="['','Remunerativo', 'Estipendio', 'Productividad', 'Med. Resid.', 'Sal. Fam.', 'Hs. Extras', 'Total']">
                        <x-reportes.totals-row :totals="$porFunci['totalFuncion']" />
                    </x-reportes.table-component>
            @endforeach

            <x-reportes.section-header :level="2">
                TOTAL FORMA DE PAGO: {{ $funcion }}
            </x-reportes.section-header>
            <x-reportes.table-component :headers="['','Remunerativo', 'Estipendio', 'Productividad', 'Med. Resid.', 'Sal. Fam.', 'Hs. Extras', 'Total']">
                <x-reportes.totals-row :totals="$porBanco['totalBanco']" />
            </x-reportes.table-component>
    @endforeach
</div>
