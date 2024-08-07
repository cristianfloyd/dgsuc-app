<div>
    <div
        class="m-4 p-4 text-center bg-white border border-gray-200 rounded-lg shadow sm:p-8 dark:bg-gray-800 dark:border-gray-700 card-body">
        @if (session()->has('message'))
            <div id="success-message" class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        <form wire:submit="save">
            <x-mary-file wire:model="archivotxt" hint="Solo TXT" accept="application/txt" />
            <div>
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

                <div>
                    <x-mary-radio  :options="$origenes" option-label="name"
                        wire:model="selectedOrigen" hint="Elige los archivos a cargar" class="bg-red-50 w-auto" />

                    <x-mary-datepicker label="Periodo Fiscal" wire:model="periodo_fiscal" icon="o-calendar"
                        :config="$config1" />
                </div>
            </div>
            <div>
                @error('archivotxt')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
            <x-mary-button class="btn-outline flex" type="submit">Guardar TXT</x-mary-button>
        </form>
    </div>
    <div
        class="m-4 p-4 text-center bg-white border border-gray-200 rounded-lg shadow sm:p-8 dark:bg-gray-800 dark:border-gray-700 card-body">
        <div class="table-responsive">
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
                            <td>
                                <x-mary-button class="btn-square ml-4 bg-gray-800  text-red-500" icon="o-trash"
                                    wire:click="deleteFile({{ $importacion->id }})">
                                </x-mary-button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </table>
        </div>
    </div>

    <div class="card-footer">
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
