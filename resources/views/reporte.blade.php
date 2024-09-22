<!-- resources/views/livewire/reporte.blade.php -->
@extends('layouts.custom')

@section('title', 'Reporte de Ventas')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])
@endsection

@section('header')
<h1>Orden de pago</h1>
@endsection

@section('content')
<div>
        <!-- Aquí el contenido del componente Livewire -->
        @livewire('livewire.reportes.reporte-orden-pago-exportable')
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/reporte.js') }}"></script>
@endsection

