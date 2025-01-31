<?php

namespace App\Services;

class ConceptosSindicatosService
{
    public static function getDosubaCodigos(): array
    {
        return [
            '206', //OBRA SOCIAL DEVOLUCIËN MOPRES	DOSUBA
            '207', //APORTE REAJUSTE DOCENTE	DOSUBA
            '210', //DOSUBA AFILIADO AGENTE	DOSUBA
            '211', //DOSUBA  PADRES	DOSUBA
            '212', //DOSUBA PRESTACION.MEDICAS	DOSUBA
            '213', //DOSUBA  PRESTAMOS	DOSUBA
            '246', //OBRA SOCIAL FAMILIAR DE HECHO	DOSUBA
            '252', //OBRA SOCIAL CONYUGE DE HECHO	DOSUBA
            '283', //DOSUBA REINTEGRO GASTOS MEDICO	DOSUBA
            '284', //DOSUBA ADHERENTES	DOSUBA
            '285', //DOSUBA HIJO MAYOR	DOSUBA
            '286', //DOSUBA ADQUIS. DE MEDICAMENTOS	DOSUBA
            '287', //DOSUBA HIJO ESTUDIANTE	DOSUBA
            '214', //PREST ALTO COSTO BAJA INCIDENC	DOSUBA Prestaciones de Alto Costo/Baja Incidencia
        ];
    }

    public static function getApubaCodigos(): array
    {
        return [
            '258', //
            '266', //
            '265'  //
        ];
    }

    public static function getFedubaCodigos(): array
    {
        return [
            '273', //
        ];
    }
}
