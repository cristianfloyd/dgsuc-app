<div>
        <div class="flex flex-col bg-white dark:bg-gray-800 max-w-4xl mx-auto p-6 sm:px-6 lg:px-8">
            <form >
                <div class="mb-4">
                    <x-label >Titulo</x-label>
                    <x-input class="w-full" type="text"  placeholder="Ingrese el titulo" />
                </div>
                <form wire:submit="importar">
                    <br>
                    <x-label >Archivos Cargados</x-label>
                    <select id="archivo" class="form-control @error('archivo') is-invalid @enderror" required>
                        @foreach ($archivos as $archivo)
                            <option  value="{{$archivo->id}}" wire:model="selectedArchivoId">
                                {{ $archivo->id }} | {{ $archivo->filename }} | {{ $archivo->user_name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="mt-4">
                        <button type="submit" >Save</button>
                    </div>
                </form>
                <div>
                    @error('archivo')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                </div>
                <div class="mb-4">
                    {{-- Aqui --}}
                </div>

                <div class="mb-4">

                </div>
                <div>
                    {{-- <input type="file" > --}}
                </div>

                <div class="flex justify-end">


                </div>
                <div class="flex justify-end">
                    {{-- <x-button wire:model="file" label="Receipt" hint="Only TXT" accept="application/txt" /> --}}
                </div>
            </form>
        </div>
</div>
