<div>
    <form method="POST" action="{{ route('toba.login') }}">
        @csrf
        <div>
            <label for="usuario">Usuario:</label>
            <input id="usuario" type="text" name="usuario" value="{{ old('usuario') }}" required>
            @error('usuario')
                <span>{{ $message }}</span>
            @enderror
        </div>
    
        <div>
            <label for="clave">Contraseña:</label>
            <input id="clave" type="password" name="clave" required>
        </div>
    
        <button type="submit">Iniciar Sesión</button>
    </form>
</div>
