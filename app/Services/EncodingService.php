<?php

namespace App\Services;

use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

class EncodingService
{
    // Constantes para las codificaciones soportadas
    private const UTF8 = 'UTF-8';
    private const LATIN1 = 'ISO-8859-1';
    private const WINDOWS1252 = 'WINDOWS-1252';
    private const ASCII = 'ASCII';

    /**
     * Convierte el valor proporcionado a codificación UTF-8.
     *
     * @param string $value El valor a convertir.
     * @return string El valor convertido a UTF-8.
     */
    // public static function toUtf8($value)
    // {
    //     if (empty($value)) return $value;

    //     // Asumimos que los datos vienen en ISO-8859-1
    //     // y los convertimos directamente a UTF-8
    //     return mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
    // }
    public static function toUtf8(?string $value): ?string
    {
        // Retorna null o string vacío sin procesar
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            // Si ya está en UTF-8, retornamos el valor original
            if (mb_detect_encoding($value, [self::UTF8], true)) {
                return $value;
            }

            // Intentamos la conversión desde Latin1 a UTF-8
            $converted = iconv(self::LATIN1, self::UTF8 . '//TRANSLIT//IGNORE', $value);

            $encoding = mb_detect_encoding($value, [self::UTF8], true);
            if ($encoding === self::ASCII) {
                return iconv(self::ASCII, self::LATIN1 . '//TRANSLIT//IGNORE', $value);
            }



            if ($converted === false) {
                throw new InvalidArgumentException('Error en la conversión a UTF-8');
            }

            return $converted;
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                "Error al procesar la codificación: {$e->getMessage()}"
            );
        }
    }

    /**
     * Convierte el valor proporcionado a codificación ISO-8859-1 (Latin1).
     *
     * @param string $value El valor a convertir.
     * @return string El valor convertido a ISO-8859-1.
     */
    public static function toLatin1(?string $value): ?string
    {
        // Retorna null o string vacío sin procesar
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            // Si está en UTF-8, convertimos a Latin1
            if (mb_detect_encoding($value, self::UTF8, true)) {
                $converted = iconv(self::UTF8, self::LATIN1 . '//TRANSLIT//IGNORE', $value);

                if ($converted === false) {
                    throw new InvalidArgumentException('Error en la conversión a Latin1');
                }

                return $converted;
            }

            return $value;
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                "Error al procesar la codificación: {$e->getMessage()}"
            );
        }
    }

    // public static function toLatin1($value)
    // {
    //     if (empty($value)) return $value;

    //     // Si el valor ya está en ISO-8859-1, lo dejamos como está
    //     if (mb_detect_encoding($value, 'ISO-8859-1', true)) {
    //         return $value;
    //     }

    //     // Convertimos de UTF-8 a ISO-8859-1
    //     return mb_convert_encoding($value, 'ISO-8859-1', 'UTF-8');
    // }
}
