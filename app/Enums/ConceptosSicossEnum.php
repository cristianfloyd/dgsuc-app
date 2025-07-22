<?php

namespace App\Enums;

/**
 * Enum para los códigos de conceptos utilizados en SICOSS.
 *
 * Este enum centraliza todos los códigos de conceptos utilizados en los cálculos
 * de aportes y contribuciones para SICOSS, asegurando coherencia en toda la aplicación.
 */
enum ConceptosSicossEnum: int
{
    // Códigos de Aportes SIJP
    case APORTE_SIJP_201 = 201;

    case APORTE_SIJP_202 = 202;

    case APORTE_SIJP_203 = 203;

    case APORTE_SIJP_403 = 403; // Agregado

    case APORTE_SIJP_204 = 204;

    case APORTE_SIJP_205 = 205;

    // Códigos de Aportes INSSJP
    case APORTE_INSSJP_247 = 247;

    case APORTE_INSSJP_447 = 447; // Agregado

    case APORTE_INSSJP_248 = 248;

    // Códigos de Contribuciones SIJP
    case CONTRIBUCION_SIJP_301 = 301;

    case CONTRIBUCION_SIJP_302 = 302;

    case CONTRIBUCION_SIJP_303 = 303;

    case CONTRIBUCION_SIJP_304 = 304;

    case CONTRIBUCION_SIJP_305 = 305;

    case CONTRIBUCION_SIJP_306 = 306;

    case CONTRIBUCION_SIJP_307 = 307;

    case CONTRIBUCION_SIJP_308 = 308;

    // Códigos de Contribuciones INSSJP
    case CONTRIBUCION_INSSJP_347 = 347;
    // case CONTRIBUCION_INSSJP_348 = 348;

    // Códigos de Exclusión
    case EXCLUSION_123 = 123;

    /**
     * Obtiene todos los códigos de aportes SIJP.
     *
     * @return array<int> Array con los códigos de aportes SIJP
     */
    public static function getAportesSijpCodes(): array
    {
        return [
            self::APORTE_SIJP_201->value,
            self::APORTE_SIJP_202->value,
            self::APORTE_SIJP_203->value,
            self::APORTE_SIJP_403->value,
            self::APORTE_SIJP_204->value,
            self::APORTE_SIJP_205->value,
        ];
    }

    /**
     * Obtiene todos los códigos de aportes INSSJP.
     *
     * @return array<int> Array con los códigos de aportes INSSJP
     */
    public static function getAportesInssjpCodes(): array
    {
        return [
            self::APORTE_INSSJP_247->value,
            self::APORTE_INSSJP_447->value,
            // self::APORTE_INSSJP_248->value
        ];
    }

    /**
     * Obtiene todos los códigos de contribuciones SIJP.
     *
     * @return array<int> Array con los códigos de contribuciones SIJP
     */
    public static function getContribucionesSijpCodes(): array
    {
        return [
            self::CONTRIBUCION_SIJP_301->value,
            self::CONTRIBUCION_SIJP_302->value,
            self::CONTRIBUCION_SIJP_303->value,
            self::CONTRIBUCION_SIJP_304->value,
            self::CONTRIBUCION_SIJP_307->value,
        ];
    }

    public static function getContribucionesArtCodes(): array
    {
        return [
            self::CONTRIBUCION_SIJP_305->value,
            self::CONTRIBUCION_SIJP_306->value,
            self::CONTRIBUCION_SIJP_308->value,
        ];
    }

    /**
     * Obtiene todos los códigos de contribuciones INSSJP.
     *
     * @return array<int> Array con los códigos de contribuciones INSSJP
     */
    public static function getContribucionesInssjpCodes(): array
    {
        return [
            self::CONTRIBUCION_INSSJP_347->value,
            // self::CONTRIBUCION_INSSJP_348->value,
        ];
    }

    /**
     * Obtiene todos los códigos de exclusión.
     *
     * @return array<int> Array con los códigos de exclusión
     */
    public static function getExclusionCodes(): array
    {
        return [
            self::EXCLUSION_123->value,
            self::APORTE_INSSJP_248->value,
        ];
    }

    /**
     * Obtiene todos los códigos de aportes (SIJP + INSSJP).
     *
     * @return array<int> Array con todos los códigos de aportes
     */
    public static function getAllAportesCodes(): array
    {
        return array_merge(
            self::getAportesSijpCodes(),
            self::getAportesInssjpCodes(),
        );
    }

    /**
     * Obtiene todos los códigos de contribuciones (SIJP + INSSJP).
     *
     * @return array<int> Array con todos los códigos de contribuciones
     */
    public static function getAllContribucionesCodes(): array
    {
        return array_merge(
            self::getContribucionesSijpCodes(),
            self::getContribucionesInssjpCodes(),
        );
    }

    /**
     * Genera la condición SQL para aportes SIJP.
     *
     * @return string Condición SQL para aportes SIJP
     */
    public static function getSqlConditionAportesSijp(): string
    {
        $codes = implode(', ', self::getAportesSijpCodes());
        return "codn_conce IN ({$codes})";
    }

    /**
     * Genera la condición SQL para aportes INSSJP.
     *
     * @return string Condición SQL para aportes INSSJP
     */
    public static function getSqlConditionAportesInssjp(): string
    {
        $codes = implode(', ', self::getAportesInssjpCodes());
        return "codn_conce IN ({$codes})";
    }

    /**
     * Genera la condición SQL para contribuciones SIJP.
     *
     * @return string Condición SQL para contribuciones SIJP
     */
    public static function getSqlConditionContribucionesSijp(): string
    {
        $codes = implode(', ', self::getContribucionesSijpCodes());
        return "codn_conce IN ({$codes})";
    }

    /**
     * Genera la condición SQL para contribuciones INSSJP.
     *
     * @return string Condición SQL para contribuciones INSSJP
     */
    public static function getSqlConditionContribucionesInssjp(): string
    {
        $codes = implode(', ', self::getContribucionesInssjpCodes());
        return "codn_conce IN ({$codes})";
    }
}
