<?php

namespace App\Models\Mapuche;

/**
 * Clase de configuración Helper para el sistema Mapuche usando el modelo Rrhhini
 */
class MapucheConfig
{
    /**
     * Obtener el valor de un parámetro de RRHH.
     *
     * @param string $section Sección principal
     * @param string $parameter Nombre del parámetro
     * @param mixed $default Valor por defecto si el parámetro no se encuentra
     * @return mixed
     */
    public static function getParametroRrhh(string $section, string $parameter, $default = null)
    {
        $value = Rrhhini::getParameterValue($section, $parameter);
        return $value ?? $default;
    }

    /**
     * Establecer el valor de un parámetro de RRHH.
     *
     * @param string $section Sección principal
     * @param string $parameter Nombre del parámetro
     * @param mixed $value Valor a establecer
     * @return bool
     */
    public static function setParametroRrhh(string $section, string $parameter, $value): bool
    {
        return Rrhhini::updateOrCreate(
            [
                'nombre_seccion' => $section,
                'nombre_parametro' => $parameter
            ],
            [
                'dato_parametro' => $value
            ]
        )->exists;
    }

    /**
     * Obtener el porcentaje de aporte diferencial de jubilación.
     *
     * Retorna el valor del porcentaje de aporte diferencial de jubilación configurado en el sistema.
     *
     * @return float El valor del porcentaje de aporte diferencial de jubilación.
     */
    public static function getPorcentajeAporteDiferencialJubilacion(): float
    {
        return floatval(self::getParametroRrhh('Porcentaje', 'PorcAporteDiferencialJubilacion', 0));
    }

    /**
     * Obtener el valor de informar becarios para SICOSS.
     *
     * Retorna el valor configurado para informar becarios en SICOSS.
     *
     * @return bool El valor de informar becarios.
     */
    public static function getSicossInformarBecarios(): bool
    {
        return (bool)self::getParametroRrhh('Sicoss', 'InformarBecario', 0);
    }

    /**
     * Obtener el valor de ART con tope.
     *
     * Retorna el valor configurado para ART con tope.
     *
     * @return string El valor de ART con tope.
     */
    public static function getSicossArtTope(): string
    {
        return self::getParametroRrhh('Sicoss', 'ARTconTope', '1');
    }

    /**
     * Obtener los conceptos no remunerativos incluidos en el ART.
     *
     * Retorna el valor de los conceptos no remunerativos incluidos en el ART, configurados en el sistema.
     *
     * @return string El valor de los conceptos no remunerativos incluidos en el ART.
     */
    public static function getSicossConceptosNoRemunerativosEnArt(): string
    {
        return self::getParametroRrhh('Sicoss', 'ConceptosNoRemuEnART', '0');
    }

    /**
     * Obtener las categorías de aportes diferenciales configuradas.
     *
     * Retorna el valor de las categorías de aportes diferenciales configuradas en el sistema.
     *
     * @return string El valor de las categorías de aportes diferenciales.
     */
    public static function getSicossCategoriasAportesDiferenciales(): string
    {
        return self::getParametroRrhh('Sicoss', 'CategoriasAportesDiferenciales', '0');
    }

    /**
     * Obtener el número de horas extras para novedades.
     *
     * Retorna el número de horas extras configuradas para novedades.
     *
     * @return int El número de horas extras.
     */
    public static function getSicossHorasExtrasNovedades(): int
    {
        return (int)self::getParametroRrhh('Sicoss', 'HorasExtrasNovedades', 0);
    }

    /**
     * Obtener los parámetros de ajustes de imputaciones contables.
     *
     * Retorna el valor del parámetro de ajustes de imputaciones contables, que indica si está habilitado o deshabilitado.
     *
     * @return string El valor del parámetro, que puede ser 'Habilitada' o 'Deshabilitada'.
     */
    public static function getParametrosAjustesImpContable(): string
	{
		return self::getParametroRrhh('Presupuesto', 'GestionAjustesImputacionesPresupuestarias','Deshabilitada');
	}
}
