<div>
    <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
        <div class="flex items-center justify-between d p-4">
            <div class="flex">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                            fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input
                        wire:model.live.debounce.500ms = "search"
                        type="text"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 "
                        placeholder="Buscar" required="">
                    </div>
            </div>
            <div class="flex space-x-3">
                <div class="flex space-x-3 items-center">
                    {{-- <label class="w-40 text-sm font-medium text-gray-900">User Type :</label> --}}
                    {{-- <select
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 ">
                        <option value="">All</option>
                        <option value="0">User</option>
                        <option value="1">Admin</option>
                    </select> --}}
                </div>
            </div>
        </div>
    </div>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg text-center bg-white border border-gray-200 rounded-lg shadow sm:p-8 dark:bg-gray-800 dark:border-gray-700 card-body">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">nro_legaj</th>
                    <th scope="col" class="px-6 py-3">nro_liqui</th>
                    <th scope="col" class="px-6 py-3">sino_cerra</th>
                    <th scope="col" class="px-6 py-3">desc_estado_liquidacion</th>
                    <th scope="col" class="px-6 py-3">nro_cargo</th>
                    <th scope="col" class="px-6 py-3">periodo_fiscal</th>
                    <th scope="col" class="px-6 py-3">tipo_registro</th>
                    <th scope="col" class="px-6 py-3">cod_mov</th>
                    <th scope="col" class="px-6 py-3">CUIL</th>
                    <th scope="col" class="px-6 py-3">marca_trab_agro</th>
                    <th scope="col" class="px-6 py-3">modalidad_contrato</th>
                    <th scope="col" class="px-6 py-3">inicio_rel_Laboral</th>
                    <th scope="col" class="px-6 py-3">fin_rel_laboral</th>
                    <th scope="col" class="px-6 py-3">codigo_de_obra_social</th>
                    <th scope="col" class="px-6 py-3">codigo_situacion_baja</th>
                    <th scope="col" class="px-6 py-3">fecha_telegrama_renuncia</th>
                    <th scope="col" class="px-6 py-3">retribución_pactada</th>
                    <th scope="col" class="px-6 py-3">modalidad_liquidación</th>
                    <th scope="col" class="px-6 py-3">sucursal_dom_desemp</th>
                    <th scope="col" class="px-6 py-3">actividad_desemp</th>
                    <th scope="col" class="px-6 py-3">Puesto_desemp</th>
                    <th scope="col" class="px-6 py-3">Rectificación</th>
                    <th scope="col" class="px-6 py-3">C_C_C_Trabajo</th>
                    <th scope="col" class="px-6 py-3">tipo_servicio</th>
                    <th scope="col" class="px-6 py-3">categoria_profesional</th>
                    <th scope="col" class="px-6 py-3">fecha_susp_servicios</th>
                    <th scope="col" class="px-6 py-3">numero_formulario_agropecuario</th>
                    <th scope="col" class="px-6 py-3">covid</th>
                </tr>
            </thead>
            <tbody>
                <!-- Las filas de datos se añadirán aquí -->
                @foreach ($datos as $resultado )
                <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                    <td class="px-6 py-4">{{ $resultado->nro_legaj }}</td>
                    <td class="px-6 py-4">{{ $resultado->nro_liqui }}</td>
                    <td class="px-6 py-4">{{ $resultado->sino_cerra }}</td>
                    <td class="px-6 py-4">{{ $resultado->desc_estado_liquidacion }}</td>
                    <td class="px-6 py-4">{{ $resultado->nro_cargo }}</td>
                    <td class="px-6 py-4">{{ $resultado->periodo_fiscal }}</td>
                    <td class="px-6 py-4">{{ $resultado->tipo_de_registro }}</td>
                    <td class="px-6 py-4">{{ $resultado->codigo_movimiento }}</td>
                    <td class="px-6 py-4">{{ $resultado->cuil }}</td>
                    <td class="px-6 py-4">{{ $resultado->trabajador_agropecuario }}</td>
                    <td class="px-6 py-4">{{ $resultado->modalidad_contrato }}</td>
                    <td class="px-6 py-4">{{ $resultado->inicio_rel_lab }}</td>
                    <td class="px-6 py-4">{{ $resultado->fin_rel_lab }}</td>
                    <td class="px-6 py-4">{{ $resultado->obra_social }}</td>
                    <td class="px-6 py-4">{{ $resultado->codigo_situacion_baja }}</td>
                    <td class="px-6 py-4">{{ $resultado->fecha_tel_renuncia }}</td>
                    <td class="px-6 py-4">{{ $resultado->retribucion_pactada }}</td>
                    <td class="px-6 py-4">{{ $resultado->modalidad_liquidaicon }}</td>
                    <td class="px-6 py-4">{{ $resultado->domicilio }}</td>
                    <td class="px-6 py-4">{{ $resultado->actividad }}</td>
                    <td class="px-6 py-4">{{ $resultado->puesto }}</td>
                    <td class="px-6 py-4">{{ $resultado->rectificacion }}</td>
                    <td class="px-6 py-4">{{ $resultado->ccct }}</td>
                    <td class="px-6 py-4">{{ $resultado->tipo_servicio }}</td>
                    <td class="px-6 py-4">{{ $resultado->categoria }}</td>
                    <td class="px-6 py-4">{{ $resultado->fecha_suspencion_servicios }}</td>
                    <td class="px-6 py-4">{{ $resultado->numero_form_agrop }}</td>
                    <td class="px-6 py-4">{{ $resultado->covid }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="py-4 px-3">
        <div class="flex">
            <div class="flex space-x-4 items-center mb-3">
                <label class="w-32 text-sm font-medium text-gray-900">Por Pag.</label>
                <select
                wire:model.live="porPagina"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 ">
                    <option value="5">5</option>
                    <option value="7">7</option>
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
        {{$datos->links()}}
    </div>
</div>
