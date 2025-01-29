<?php

namespace App\Models\Mapuche;

/**
 * Configuration helper class for Mapuche system using Rrhhini model
 */
class MapucheConfig
{
    /**
     * Get RRHH parameter value
     *
     * @param string $section Main section
     * @param string $parameter Parameter name
     * @param mixed $default Default value if parameter not found
     * @return mixed
     */
    public static function getParametroRrhh(string $section, string $parameter, $default = null)
    {
        $value = Rrhhini::getParameterValue($section, $parameter);
        return $value ?? $default;
    }

    /**
     * Set RRHH parameter value
     *
     * @param string $section Main section
     * @param string $parameter Parameter name
     * @param mixed $value Value to set
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
     * Get percentage for differential retirement contribution
     *
     * @return float
     */
    public static function getPorcentajeAporteDiferencialJubilacion(): float
    {
        return floatval(self::getParametroRrhh('Porcentaje', 'PorcAporteDiferencialJubilacion', 0));
    }

    /**
     * Get SICOSS report becarios parameter
     *
     * @return bool
     */
    public static function getSicossInformarBecarios(): bool
    {
        return (bool)self::getParametroRrhh('Sicoss', 'InformarBecario', 0);
    }

    /**
     * Get SICOSS ART with limit parameter
     *
     * @return string
     */
    public static function getSicossArtTope(): string
    {
        return self::getParametroRrhh('Sicoss', 'ARTconTope', '1');
    }

    /**
     * Get non-remunerative concepts in ART parameter
     *
     * @return string
     */
    public static function getSicossConceptosNoRemunerativosEnArt(): string
    {
        return self::getParametroRrhh('Sicoss', 'ConceptosNoRemuEnART', '0');
    }

    /**
     * Get differential contributions categories parameter
     *
     * @return string
     */
    public static function getSicossCategoriasAportesDiferenciales(): string
    {
        return self::getParametroRrhh('Sicoss', 'CategoriasAportesDiferenciales', '0');
    }

    /**
     * Get extra hours novelties parameter
     *
     * @return int
     */
    public static function getSicossHorasExtrasNovedades(): int
    {
        return (int)self::getParametroRrhh('Sicoss', 'HorasExtrasNovedades', 0);
    }
}
