<!-- resources/views/upload.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>upload</title>
</head>
<body>
    @if (session('success'))
    <div>{{ session('success') }}</div>
    <br>
@endif
@if (session('error'))
    <div>{{ session('error') }}</div>
    <br>
@endif
<form action="{{ route('upload-sicoss') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file" accept=".txt">
    <button type="submit">Subir</button>
    <button type="submit">Transformar</button>
</form>
@if (session('success'))
    <div>
        {{ session('success') }}
    </div>
@endif
<form method="POST" action="{{ route('transformar') }}">
    @csrf
    <button type="submit">Procesar Datos</button>
</form>
</body>
</html>

