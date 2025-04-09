<div 
    x-show="$wire.isHelpVisible"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform -translate-y-2"
    x-transition:enter-end="opacity-100 transform translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 transform -translate-y-2"
    class="rounded-xl bg-white shadow-lg ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700 p-2"
>
    <div class="p-6">
        <div class="mt-4 space-y-4">
            <h2 class="text-xl font-bold text-gray-900 dark:!text-white">{{ __('Guía de Usuario: Actualización SICOSS') }}</h2>
            <p class="text-gray-700 dark:text-gray-200">La herramienta de actualización SICOSS permite a los usuarios actualizar y verificar datos necesarios para la generación de archivos SICOSS. Este proceso asegura que los datos de los agentes estén correctamente categorizados.</p>

            <h3 class="text-lg font-semibold text-gray-900 dark:!text-white">{{ __('Acceso al Sistema') }}</h3>
            <ul class="list-disc pl-5 text-gray-700 dark:text-gray-200">
                <li><strong>{{ __('Ruta de Acceso') }}</strong>: /afip-panel/sicoss-updates</li>
                <li><strong>{{ __('Navegación') }}</strong>: AFIP > Actualización SICOSS</li>
                <li><strong>{{ __('Permisos') }}</strong>: Necesitas acceso al panel AFIP para utilizar esta herramienta.</li>
            </ul>

            <h3 class="text-lg font-semibold text-gray-900 dark:!text-white">{{ __('Proceso de Actualización') }}</h3>
            <ol class="list-decimal pl-5 text-gray-700 dark:text-gray-200">
                <li><strong>{{ __('Seleccionar Liquidaciones') }}</strong>: Usa el widget de selección para elegir las liquidaciones que deseas procesar. Filtra por período fiscal si es necesario.</li>
                <li><strong>{{ __('Ejecutar Actualizaciones') }}</strong>: Haz clic en "Ejecutar Actualizaciones" para iniciar el proceso. El sistema determinará automáticamente si debe usar tablas actuales o históricas.</li>
                <li><strong>{{ __('Revisar Resultados') }}</strong>: Observa el progreso y los resultados detallados. Verifica si hay agentes sin código de actividad.</li>
            </ol>

            <!-- Sección de ayuda para actualización de embarazadas -->
            <h3 class="text-lg font-semibold text-gray-900 dark:!text-white">{{ __('Actualización de Embarazadas') }}</h3>
            <p class="text-gray-700 dark:text-gray-200">
                Esta funcionalidad permite actualizar específicamente la situación de revista para agentes con licencia por embarazo en el sistema SICOSS.
            </p>
            <ol class="list-decimal pl-5 text-gray-700 dark:text-gray-200">
                <li><strong>{{ __('Seleccionar Período Fiscal') }}</strong>: Asegúrate de tener el período fiscal correcto seleccionado.</li>
                <li><strong>{{ __('Ejecutar Actualización de Embarazadas') }}</strong>: Haz clic en el botón "Actualizar Embarazadas" para iniciar el proceso específico.</li>
                <li><strong>{{ __('Proceso Interno') }}</strong>: El sistema identificará automáticamente a las agentas con licencia por maternidad (código 7) y actualizará su situación de revista en SICOSS.</li>
                <li><strong>{{ __('Verificación') }}</strong>: Los resultados mostrarán la cantidad de registros actualizados y el tiempo de procesamiento.</li>
            </ol>
            <div class="bg-amber-50 p-3 rounded-lg text-amber-800 dark:bg-amber-900 dark:text-amber-200 mt-2">
                <p class="font-medium">{{ __('Nota importante') }}:</p>
                <p>Esta actualización modifica los siguientes campos en la tabla SICOSS:</p>
                <ul class="list-disc pl-5">
                    <li>cod_situacion: Establece el código de situación de revista</li>
                    <li>sit_rev1, sit_rev2, sit_rev3: Actualiza las situaciones de revista por períodos</li>
                    <li>dia_ini_sit_rev1, dia_ini_sit_rev2, dia_ini_sit_rev3: Establece los días de inicio de cada situación</li>
                </ul>
            </div>

            <h3 class="text-lg font-semibold text-gray-900 dark:!text-white">{{ __('Resultados y Feedback') }}</h3>
            <ul class="list-disc pl-5 text-gray-700 dark:text-gray-200">
                <li><strong>{{ __('Indicador de Progreso') }}</strong>: Muestra el avance del proceso.</li>
                <li><strong>{{ __('Resultados Detallados') }}</strong>: Incluye un listado de liquidaciones seleccionadas y agentes sin actividad.</li>
                <li><strong>{{ __('Notificaciones') }}</strong>: Recibirás mensajes de éxito o error al finalizar el proceso.</li>
            </ul>

            <h3 class="text-lg font-semibold text-gray-900 dark:!text-white">{{ __('Consideraciones Técnicas') }}</h3>
            <ul class="list-disc pl-5 text-gray-700 dark:text-gray-200">
                <li><strong>{{ __('Seguridad') }}</strong>: El acceso está controlado mediante permisos en el panel.</li>
                <li><strong>{{ __('Performance') }}</strong>: El sistema está optimizado para procesar grandes cantidades de datos de manera eficiente.</li>
            </ul>

            <h3 class="text-lg font-semibold text-gray-900 dark:!text-white">{{ __('Soporte') }}</h3>
            <p class="text-gray-700 dark:text-gray-200">{{ __('Para asistencia adicional, contacta al equipo de soporte técnico ...') }}</p>
        </div>
    </div>
</div>
