<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Documentación del Sistema</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        h1 { font-size: 24px; }
        h2 { font-size: 20px; }
        h3 { font-size: 16px; }
        .page-break { page-break-after: always; }
        .section { margin-bottom: 20px; }
    </style>
</head>
<body>
    @foreach($documentation as $doc)
        <div class="section">
            <h2>{{ $doc['title'] }}</h2>
            {!! $doc['rendered_content'] !!}
        </div>
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
