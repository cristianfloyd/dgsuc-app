            <!-- Sección de botones -->
            <div class="w-full grid grid-cols-2">
                @if ($latestWorkflow = $this->workflowService->getLatestWorkflow())
                    {{-- <button wire:click="completeStep('obtener_cuils_not_in_afip')" class="btn btn-primary @if($latestWorkflow && $this->workflowService->isStepCompleted($latestWorkflow, 'poblar_tabla_temp_cuils')) disabled @endif">Poblar Tabla Temp CUILs</button> --}}
                    <div class="col-span-2 md:flex justify-center py-3">

                        @if ($this->workflowService->isStepCompleted($latestWorkflow, 'subir_archivo_afip'))
                            {{-- <button wire:click="importArchive('subir_archivo_mapuche')" class="btn btn-primary mr-4">Importar Archivo Mapuche</button> --}}

                                <div class="w-full flex justify-center px-1 py-2 space-x-2">
                                    @if($this->workflowService->isStepCompleted($latestWorkflow, 'subir_archivo_afip'))
                                        {{-- <button wire:click="importArchive('subir_archivo_mapuche')" class="btn btn-primary mr-4">Importar Archivo Mapuche</button> --}}

                                    @endif

                                    <div class="w-full md:flex justify-center px-1 py-2 space-x-3 items-center rounded shadow-md bg-white">
                                        {{-- <h4 class="text-lg font-bold text-gray-700 pb-6 mt-4 mb-8 w-full"><i class='bx bxs-download'></i> Descargar archivo</h4> --}}

                                        <div class="w-full md:flex justify-center px-1 py-2 space-x-3">
                                            @if($this->workflowService->isStepCompleted($latestWorkflow, 'obtener_cuils_not_in_afip'))

                                                <a href="{{ route('download.results') }}" class="btn btn-primary mr-4">Descargar Archivo</a>
                                            @endif
                                        </div>

                                    </div>

                                </div>

                        @elseif($this->workflowService->isStepCompleted($latestWorkflow, 'import_archivo_afip'))
                            <a href="{{ route('download.results') }}" class="btn btn-primary">Descargar Archivo</button>
                        @endif

                    </div>
