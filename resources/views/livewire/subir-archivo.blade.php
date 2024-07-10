<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <form wire:submit="guardar">
                    <x-mary-file wire:model="archivo" label="Receipt" hint="Only TXT" accept="application/txt" />
                    @php
                        $config1 = [
                            'plugins' => [
                                [
                                    'monthSelectPlugin' => [
                                        'dateFormat' => 'Ym',
                                        'altFormat' => 'Ym',
                                    ],
                                ],
                            ],
                        ];
                    @endphp

                    {{-- <div>
                        <label for="origen">Seleccionar origen:</label><br>
                        <input type="radio" id="afip" name="origen" value="afip">
                        <label for="afip">AFIP</label><br>
                        <input type="radio" id="mapuche" name="origen" value="mapuche">
                        <label for="mapuche">Mapuche</label>
                    </div> --}}

                    <x-mary-radio label="Seleccionar origen" :options="$origenes" option-label="name"
                        wire:model="selectedOrigen" hint="Elige bien" class="bg-red-50 w-auto" />

                    <x-mary-datepicker label="Periodo Fiscal" wire:model="periodo_fiscal" icon="o-calendar"
                        :config="$config1" />

                    {{-- <input type="file" wire:model="archivo" /> --}}
                    {{-- //Aqui necesito un campo para especificar el periodo fiscal, el cual sera el año y mes. --}}
                    {{-- // Deberia ser una lista de opciones de año y mes. Entonces armar la variabla $periodo_fiscal = 202405 --}}

                    <x-mary-button class="btn-primary" type="submit">
                        Guardar
                    </x-mary-button>
                </form>
            </div>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">

            </div>
        </div>
        <div class="m-4 p-4 text-center bg-white border border-gray-200 rounded-lg shadow sm:p-8 dark:bg-gray-800 dark:border-gray-700 card-body">
            <div class="table-responsive">
                @if ($importaciones == null )
                    <div class="alert alert-warning" role="alert">
                        No hay registros
                    </div>
                @else
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Periodo Fiscal</th>
                            <th>Origen</th>
                            <th>Filename</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($importaciones as $importacion)
                        <tr>
                            <td>{{ $importacion->id }}</td>
                            <td>{{ $importacion->periodo_fiscal }}</td>
                            <td>{{ $importacion->origen }}</td>
                            <td>{{ $importacion->filename }}</td>
                            <td>{{ $importacion->user_name }}</td>

                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>

@push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://unpkg.com/flatpickr/dist/plugins/monthSelect/style.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://unpkg.com/flatpickr/dist/plugins/monthSelect/index.js"></script>
@endpush
