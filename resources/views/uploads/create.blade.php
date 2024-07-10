<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">
    <script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>

    <title>Sube sube sube</title>
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
                <div class="column">
                    <h1 class="title">Cargar Archivo</h1>
                    @if ($errors->any())
                        <div class="notification is-danger is-light">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('uploads.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label class="is-block mb-4">
                            <span class="is-block mb-2">Seleccionar archivo para subir</span>
                            <span class="file has-name is-fullwidth">
                                <label class="file-label">
                                    <input type="file" name="file_upload"/>
                                </label>
                            </span>
                        </label>
                        <div class="field is-grouped mt-3">
                            <div class="control">
                                <button type="submit" class="button is-info">Upload</button>
                            </div>
                            <div class="control">
                                <a href="{{ route('uploads.index') }}" class="button is-light">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="content has-text-centered is-flex-align-items-flex-end mt-auto">
            <p>
                Made with ❤
        </div>
    </footer>

</body>
</html>

