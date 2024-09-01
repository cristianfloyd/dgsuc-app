<?php

namespace App\Enums;

enum WorkflowStatus: string
{
    case OBTENER_CUILS_NOT_IN_AFIP = 'obtener_cuils_not_in_afip';
    case POBLAR_TABLA_TEMP_CUILS = 'poblar_tabla_temp_cuils';
    case EJECUTAR_FUNCION_ALMACENADA = 'ejecutar_funcion_almacenada';
    case OBTENER_CUILS_NO_INSERTADOS = 'obtener_cuils_no_insertados';
    case EXPORTAR_TXT_PARA_AFIP = 'exportar_txt_para_afip';
}
