<div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
    <div class="px-4 py-5 sm:px-6 flex items-center">
        <div class="flex-shrink-0">
            <svg class="h-12 w-12 text-danger-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <div class="ml-5">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                Error en el procesamiento
            </h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
                Se ha producido un error durante el procesamiento de los bloqueos.
            </p>
        </div>
    </div>
    
    <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-5 sm:p-6">
        <div class="bg-danger-50 dark:bg-danger-900/20 border-l-4 border-danger-400 dark:border-danger-600 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-danger-400 dark:text-danger-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-danger-800 dark:text-danger-200">
                        Detalles del error
                    </h3>
                    <div class="mt-2 text-sm text-danger-700 dark:text-danger-300">
                        <p>
                            {{ $mensaje ?? 'Se ha producido un error inesperado durante el procesamiento de los bloqueos.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">¿Qué puedo hacer?</h4>
            <ul class="mt-3 list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-2">
                <li>Verificar que los datos de entrada sean correctos</li>
                <li>Comprobar la conexión con el sistema Mapuche</li>
                <li>Revisar los registros de errores para más detalles</li>
                <li>Contactar al administrador del sistema si el problema persiste</li>
            </ul>
        </div>
    </div>
    
    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 text-right sm:px-6 flex justify-end space-x-3">
        <a href="{{ route('filament.reportes.resources.bloqueos.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al listado
        </a>
        
        <button type="button" onclick="window.location.reload()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Reintentar
        </button>
    </div>
</div>