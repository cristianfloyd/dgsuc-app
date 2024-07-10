
@extends('layouts.default')

@section('content')
    <div class="container">
        <h1>Registros AFIP SICOSS desde Mapuche</h1>

        <a href="" class="btn btn-primary mb-3">Nuevo Registro</a>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Periodo Fiscal</th>
                    <th>CUIL</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registros as $registro)
                    <tr>
                        <td>{{ $registro->periodo_fiscal }}</td>
                        <td>{{ $registro->CUIL }}</td>
                        <td>{{ $registro->apnom }}</td>
                        <td>
                            <a href="" class="btn btn-sm btn-primary">Ver</a>
                            <a href="" class="btn btn-sm btn-secondary">Editar</a>
                            <form action="" method="POST" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este registro?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
