<div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Importar Archivo') }}</div>

                    <div class="card-body">
                        <form wire:submit.prevent="importar">
                            @csrf

                            <div class="form-group row">
                                <label for="modelo" class="col-md-4 col-form-label text-md-right">{{ __('Modelo') }}</label>

                                <div class="col-md-6">
                                    <select id="modelo" class="form-control @error('modelo') is-invalid @enderror" wire:model="modelo" required>
                                        <option value="afip_importacion_cruda">AfipImportacionCruda</option>
                                    </select>

                                    @error('modelo')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="archivo" class="col-md-4 col-form-label text-md-right">{{ __('Archivo') }}</label>

                                <div class="col-md-6">
                                    <select id="archivo" class="form-control @error('archivo') is-invalid @enderror" wire:model="archivo" required>
                                        @foreach ($archivos as $archivo)
                                            <option value="{{ $archivo->filename }}">{{ $archivo->original_name }}</option>
                                        @endforeach
                                    </select>

                                    @error('archivo')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Importar') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
