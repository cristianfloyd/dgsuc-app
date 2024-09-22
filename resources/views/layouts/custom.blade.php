<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- <title>Informe - {{ config('app.name') }}</title> --}}
    {{-- Importa tus estilos personalizados --}}
    @section('styles')
    <link rel="stylesheet" href="{{ asset('css/reportes.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endsection
</head>
<body>
    <header>
        {{-- Define tu encabezado personalizado aquí --}}
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
