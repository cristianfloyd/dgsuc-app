<!-- resources/views/livewire/reporte.blade.php -->
@extends('layouts.custom')

@section('title', 'Orden de Pago')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/reporte.css') }}">
@endsection

@section('header')

@endsection
@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles()
@section('content')
<div>
    <!-- Aquí iría el contenido del componente Livewire -->
        @livewire('reportes.orden-pago-reporte')
    @livewireScripts()
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/reporte.js') }}"></script>
@endsection
