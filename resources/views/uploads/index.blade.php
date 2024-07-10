<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
    <title>SUC uploads</title>
</head>
<body>
    <header class="hero is-info">
        <div class="hero-body">
            <div class="container">
                <h1 class="title">
                    SUC uploads.
                </h1>
            </div>
        </div>
    </header>
    <section class="section">
        <div class="container">
            <div class="columns">
                @if(session()->has('success'))
                <div class="column">
                    <div class="alert alert-success">
                        <div class="notification is-success is-light">
                            {{ session()->get('success') }}
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="columns">

                <div class="column">
                    <h2 class="title">
                        Uploads
                    </h2>
                    <table class="table is-striped">
                        <thead>
                        <tr>
                            <th>Archivo</th>
                            <th>Subido el</th>
                            <th>Usuario</th>
                            <th>Descargar</th>
                        </tr>
                        </thead>
                        {{-- {{dd($archivos)}} --}}
                        <tbody>
                        @forelse($archivos as $archivo)
                            <tr>
                                <td>
                                    {{ $archivo->original_name }}
                                </td>
                                <td>
                                    {{ $archivo->created_at }}
                                </td>
                                <td>
                                    {{ $archivo->user_name }}
                                </td>
                                <td>
                                    {{-- <a href="{{ $archivo->urlDescarga }}" target="_blank" class="button is-link is-small"> --}}
                                    <a href="{{ route('uploads.descargar', ['archivo' => $archivo->filename])  }}" class="button is-link is-small">
                                        <span class="icon is-small">
                                            <i class="fa fa-download" aria-hidden="true"></i>
                                        </span>
                                        <span>Descargar</span>
                                    </a>
                                    {{-- <div>
                                        <a href="{{ route('descargar', ['archivo' => $archivo->filename])  }}" class="btn btn-primary" download>Descargar</a>
                                    </div> --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td>No se encontraron archivos cargados</td>
                            </tr>
                        @endforelse

                        </tbody>
                    </table>
                    <a href="{{ route('uploads.create') }}" class="button is-primary is-small">
                        <span class="icon is-small">
                            <i class="fa fa-upload" aria-hidden="true"></i>
                        </span>
                        <span>Subir un archivo</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="content has-text-centered is-flex-align-items-flex-end mt-auto">
            <p>
                Made with ❤
            </p>
        </div>
    </footer>
</body>
</html>
