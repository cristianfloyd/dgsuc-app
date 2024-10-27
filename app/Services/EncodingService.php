<?php

namespace App\Services;

class EncodingService
{
    /**
     * Convierte el valor proporcionado a codificación UTF-8.
     *
     * @param string $value El valor a convertir.
     * @return string El valor convertido a UTF-8.
     */
    public static function toUtf8($value)
    {
        if (empty($value)) return $value;

        // Detectamos si el valor ya está en UTF-8
        if (mb_detect_encoding($value, 'UTF-8', true)) {
            return $value;
        }

        // Convertimos desde ISO-8859-1 a UTF-8
        return iconv('ISO-8859-1', 'UTF-8//TRANSLIT', $value);
    }

    /**
     * Convierte el valor proporcionado a codificación ISO-8859-1 (Latin1).
     *
     * @param string $value El valor a convertir.
     * @return string El valor convertido a ISO-8859-1.
     */
    public static function toLatin1($value)
    {
        if (empty($value)) return $value;

        // Si está en UTF-8, convertimos a ISO-8859-1
        if (mb_detect_encoding($value, 'UTF-8', true)) {
            return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $value);
        }

        return $value;
    }
}
