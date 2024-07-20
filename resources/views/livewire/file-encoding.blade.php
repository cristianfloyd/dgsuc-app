<div>
    <h1>Seleccionar archivo para detectar codificación</h1>
    <div>
        <label for="file">Seleccionar archivo:</label>
        <select wire:model.live="selectedFile" id="file">
            <option selected>--Seleccione un archivo--</option>
            @foreach($files as $file)
                <option value="{{ $file->id }}">{{ $file->file_path }}</option>
            @endforeach
        </select>
    </div>

    @if($fileEncoding)
        <div>
            <h2>Codificación del archivo:</h2>
            <p>{{ $fileEncoding }}</p>
        </div>
    @endif

    <div>
        <h2>Codificación del sistema:</h2>
        <p>{{ $systemEncoding }}</p>
    </div>
    <div>
        <h2>Codificación de la base de datos:</h2>
        <p>{{ $databaseEncoding }}</p>
    </div>
    <div>
        <h1>Convertir cadena a codificación de la base de datos</h1>
        <form wire:submit.prevent="convertToDatabaseEncoding">
            <label for="inputString">Cadena de entrada:</label>
            <input type="text" id="inputString" wire:model="inputString">
            <button type="submit">Convertir</button>
        </form>

        @if($convertedString)
            <div>
                <h2>Cadena convertida:</h2>
                <p>{{ $convertedString }}</p>
            </div>
        @endif
        @if($characterCount !== null)
            <div>
                <h2>Cantidad de caracteres:</h2>
                <p>{{ $characterCount }}</p>
            </div>
        @endif
    </div>
</div>
