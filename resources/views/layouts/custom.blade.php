<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe - {{ config('app.name') }}</title>
    {{-- Importa tus estilos personalizados --}}
    @section('styles')
    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}">
    @endsection
</head>
<body>
    <header>
        {{-- Define tu encabezado personalizado aquí --}}
        <h1>Informes - {{ config('app.name') }}</h1>
    </header>

    <main>
        {{-- Aquí se cargará la vista de Livewire --}}
        @yield('content')
    </main>

    <footer>
        {{-- Define tu pie de página si lo necesitas --}}
    </footer>
</body>
</html>
