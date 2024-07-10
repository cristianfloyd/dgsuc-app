@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Detalles de la importación') }}</div>

                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Línea completa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($afip_importacion_cruda_model->all() as $linea)
                                    <tr>
                                        {{-- <td>{{ $linea->id }}</td> --}}
                                        <td>{{ $linea->linea_completa }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
