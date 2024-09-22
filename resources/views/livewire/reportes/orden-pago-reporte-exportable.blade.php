<div class="container mx-auto w-full bg-white p-6 print:p-2" label="reporteOP">
    <div class="flex flex-col space-y-1 print:space-y-2">
        <div class="mb-6 h-36 flex justify-between items-center">
            <div class="flex justify-between items-center print:text-sm">
                <img src="{{ $reportHeader['logoPath'] }}" alt="Logo" class="h-36">
            </div>
            <div class="w-1/3 text-center h-36 pt-7">
                <h1 class="text-2xl text-slate-950 font-bold">Orden de Pago: {{ $reportHeader['orderNumber'] }}</h1>
                <p>Liquidación: {{ $reportHeader['liquidationNumber'] }} - {{ $reportHeader['liquidationDescription'] }}
                </p>
            </div>
            <div class="w-1/3 text-right h-36 mt-2">
                <p>Fecha de generación: {{ $reportHeader['generationDate'] }}</p>
            </div>
        </div>
        <div class="overflow-x-auto print:overflow-x-visible">
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
                            <x-reportes.section-header :level="5" type="uacad">
                                UNIDAD ACADÉMICA: {{ $uacad }}
                            </x-reportes.section-header>
                            @foreach ($porUacad['caracteres'] as $caracter => $data)
                                <x-reportes.section-header :level="6" type="caracter">
                                    modalidad: {{ $caracter == 'CONT' ? 'contratado' : 'permanente' }}
                                </x-reportes.section-header>
                                <div class="w-full table-auto print:text-xs"">
                                    <x-reportes.table-component :headers="[
                                        'Programa',
                                        'Sueldo',
                                        'Estipendio',
                                        'Productividad',
                                        'Méd. Resid.',
                                        'Sal. Fam.',
                                        'Hs. Extras',
                                        'Total',
                                    ]">
                                        <div class="w-full table-auto print:text-xs">
                                            @foreach ($data['items'] as $item)
                                                <tr>
                                                    <td
                                                        class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right pr-1">
                                                        {{ $item->codn_progr }}</td>
                                                    <td
                                                        class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right pr-1">
                                                        {{ money($item->remunerativo) }}</td>
                                                    <td
                                                        class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right pr-1">
                                                        {{ money($item->estipendio) }}</td>
                                                    <td
                                                        class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right pr-1">
                                                        {{ money($item->productividad) }}</td>
                                                    <td
                                                        class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right pr-1">
                                                        {{ money($item->med_resid) }}</td>
                                                    <td
                                                        class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right pr-1">
                                                        {{ money($item->sal_fam) }}</td>
                                                    <td
                                                        class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right pr-1">
                                                        {{ money($item->hs_extras) }}</td>
                                                    <td
                                                        class="bg-gray-200 font-bold text-gray-900 border border-gray-600 text-right pr-1">
                                                        {{ money($item->total) }}</td>
                                                </tr>
                                            @endforeach
                                        </div>
                                        <x-reportes.totals-row :totals="$data['totals']" type="section" />
                                    </x-reportes.table-component>
                                </div>
                            @endforeach
                            <x-reportes.table-component class="b-slate-500" :headers="[
                                'Total: ' . $uacad . '',
                                'Sueldo',
                                'Estipendio',
                                'Productividad',
                                'Med. Resid.',
                                'Sal. Fam.',
                                'Hs. Extras',
                                'Total',
                            ]" type="uacad">
                                <x-reportes.totals-row :totals="$porUacad['totalUacad']" type="uacad" />
                            </x-reportes.table-component>
                        @endforeach
                        <x-reportes.section-header :level="4" type="fuente">
                            TOTAL FUENTE DE FINANCIAMIENTO: {{ $fuente }}
                        </x-reportes.section-header>
                        <x-reportes.table-component :headers="[
                            '',
                            'Remunerativo',
                            'Estipendio',
                            'Productividad',
                            'Med. Resid.',
                            'Sal. Fam.',
                            'Hs. Extras',
                            'Total',
                        ]" type="fuente">
                            <x-reportes.totals-row :totals="$porFuente['totalFuente']" type="fuente" />
                        </x-reportes.table-component>
                    @endforeach
                    <x-reportes.section-header :level="3">
                        TOTAL FUNCIÓN: {{ $funcion }}
                    </x-reportes.section-header>
                    <x-reportes.table-component :headers="[
                        '',
                        'Remunerativo',
                        'Estipendio',
                        'Productividad',
                        'Med. Resid.',
                        'Sal. Fam.',
                        'Hs. Extras',
                        'Total',
                    ]">
                        <x-reportes.totals-row :totals="$porFunci['totalFuncion']" type="funcion" />
                    </x-reportes.table-component>
                @endforeach
                <x-reportes.section-header :level="2">
                    TOTAL FORMA DE PAGO: {{ $funcion == 1 ? 'BANCO' : 'EFECTIVO' }}
                </x-reportes.section-header>
                <x-reportes.table-component :headers="[
                    '',
                    'Remunerativo',
                    'Estipendio',
                    'Productividad',
                    'Med. Resid.',
                    'Sal. Fam.',
                    'Hs. Extras',
                    'Total',
                ]">
                    <x-reportes.totals-row :totals="$porBanco['totalBanco']" type="banco" />
                </x-reportes.table-component>
            @endforeach
        </div>

        <!-- Sección de Totales Generales -->
        <div class="mt-6 print:mt-2 print:mx-auto print:w-full">
            <x-reportes.section-header :level="2" type="generales">
                TOTALES GENERALES
            </x-reportes.section-header>
            @foreach (['banco' => 'BANCO', 'efectivo' => 'EFECTIVO'] as $formaPago => $titulo)
                <x-reportes.section-header :level="1">
                    FORMA DE PAGO {{ $titulo }}
                </x-reportes.section-header>
                <div class="w-full table-auto print:text-xs">
                    <x-reportes.table-component :headers="[
                        'Func.',
                        'Finan. e Inciso',
                        'Sueldo',
                        'Estipendio',
                        'Productividad',
                        'Méd. Resid.',
                        'Sal. Fam.',
                        'Hs. Extras',
                        'Total',
                    ]" type="generales">
                        @foreach ($totalesPorFinanciamiento[$formaPago] as $funcion => $porFuncion)
                            @foreach ($porFuncion as $financiamiento => $datos)
                                <tr>
                                    <td class="text-right text-gray-950 font-bold print:font-normal print:text-sm print:text-black border-2 border-gray-900 pr-2">{{ $funcion }}</td>
                                    <td class="text-right text-gray-950 font-bold print:font-normal print:text-sm print:text-black border-2 border-gray-900 pr-2">{{ $financiamiento }}</td>
                                    <td class="text-right text-gray-950 font-bold print:font-normal print:text-sm print:text-black border-2 border-gray-900 pr-2">{{ money($datos['remunerativo']) }}</td>
                                    <td class="text-right text-gray-950 font-bold print:font-normal print:text-sm print:text-black border-2 border-gray-900 pr-2">{{ money($datos['estipendio']) }}</td>
                                    <td class="text-right text-gray-950 font-bold print:font-normal print:text-sm print:text-black border-2 border-gray-900 pr-2">{{ money($datos['productividad']) }}</td>
                                    <td class="text-right text-gray-950 font-bold print:font-normal print:text-sm print:text-black border-2 border-gray-900 pr-2">{{ money($datos['med_resid']) }}</td>
                                    <td class="text-right text-gray-950 font-bold print:font-normal print:text-sm print:text-black border-2 border-gray-900 pr-2">{{ money($datos['sal_fam']) }}</td>
                                    <td class="text-right text-gray-950 font-bold print:font-normal print:text-sm print:text-black border-2 border-gray-900 pr-2">{{ money($datos['hs_extras']) }}</td>
                                    <td class="text-right text-gray-950 font-bold print:font-normal print:text-sm print:text-black border-2 border-gray-900 pr-2">{{ money($datos['total']) }}</td>
                                </tr>
                            @endforeach
                            <x-reportes.totals-row :totals="$totalesPorFuncion[$formaPago][$funcion]" type="subtotal-funcion" />
                        @endforeach
                        <x-reportes.totals-row :totals="$totalesPorFormaPago[$formaPago]" type="subtotal-forma-pago" />
                    </x-reportes.table-component>
                </div>
            @endforeach
            <x-reportes.section-header :level="2">
                TOTAL GENERAL
            </x-reportes.section-header>
            <x-reportes.table-component :headers="['Sueldo', 'Estipendio', 'Productividad', 'Méd. Resid.', 'Sal. Fam.', 'Hs. Extras', 'Total']">
                <x-reportes.totals-row :totals="$totalGeneral" type="total-general" />
            </x-reportes.table-component>
        </div>
    </div>
    <style>
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .container {
                width: 100% !important;
                max-width: none !important;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>

</div>
