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
    // public static function toUtf8(?string $value): ?string
    // {
    //     // Retorna null o string vacío sin procesar
    //     if ($value === null || $value === '') {
    //         return $value;
    //     }

    //     try {
    //         // Si ya está en UTF-8, retornamos el valor original
    //         if (mb_detect_encoding($value, [self::UTF8], true)) {
    //             return $value;
    //         }

    //         // Intentamos la conversión desde Latin1 a UTF-8
    //         $converted = iconv(self::LATIN1, self::UTF8 . '//TRANSLIT//IGNORE', $value);

    //         $encoding = mb_detect_encoding($value, [self::UTF8], true);
    //         if ($encoding === self::ASCII) {
    //             return iconv(self::ASCII, self::LATIN1 . '//TRANSLIT//IGNORE', $value);
    //         }



    //         if ($converted === false) {
    //             throw new InvalidArgumentException('Error en la conversión a UTF-8');
    //         }

    //         return $converted;
    //     } catch (\Exception $e) {
    //         throw new InvalidArgumentException(
    //             "Error al procesar la codificación: {$e->getMessage()}"
    //         );
    //     }
    // }

    /**
     * Convierte el valor proporcionado a codificación ISO-8859-1 (Latin1).
     *
     * @param string $value El valor a convertir.
     * @return string El valor convertido a ISO-8859-1.
     */
    // public static function toLatin1(?string $value): ?string
    // {
    //     // Retorna null o string vacío sin procesar
    //     if ($value === null || $value === '') {
    //         return $value;
    //     }

    //     try {
    //         // Si está en UTF-8, convertimos a Latin1
    //         if (mb_detect_encoding($value, self::UTF8, true)) {
    //             $converted = iconv(self::UTF8, self::LATIN1 . '//TRANSLIT//IGNORE', $value);

    //             if ($converted === false) {
    //                 throw new InvalidArgumentException('Error en la conversión a Latin1');
    //             }

    //             return $converted;
    //         }

    //         return $value;
    //     } catch (\Exception $e) {
    //         throw new InvalidArgumentException(
    //             "Error al procesar la codificación: {$e->getMessage()}"
    //         );
    //     }
    // }

    // Función de conversión mejorada
    // function latin1_to_utf8($text) {
    //     // Mapa de caracteres especiales comunes en español
    //     $char_map = [
    //         "\xCD" => "Í", // I con acento
    //         "\xD1" => "Ñ", // Ñ
    //         "\xC1" => "Á", // A con acento
    //         "\xC9" => "É", // E con acento
    //         "\xD3" => "Ó", // O con acento
    //         "\xDA" => "Ú", // U con acento
    //         // También sus versiones minúsculas
    //         "\xED" => "í",
    //         "\xF1" => "ñ",
    //         "\xE1" => "á",
    //         "\xE9" => "é",
    //         "\xF3" => "ó",
    //         "\xFA" => "ú"
    //     ];

    //     return strtr($text, $char_map);
    // }

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

    /**
     * Convierte el valor proporcionado a codificación UTF-8.
     * Maneja específicamente caracteres latinos problemáticos.
     *
     * @param string|null $value El valor a convertir.
     * @return string|null El valor convertido a UTF-8.
     */
    public static function toUtf8(?string $value): ?string
    {
        // Retorna null o string vacío sin procesar
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            // Caracteres especiales comunes en español y sus equivalentes UTF-8
            $char_map = [
                // Vocales con acento
                "\xCD" => "Í", // I con acento
                "\xC1" => "Á", // A con acento
                "\xC9" => "É", // E con acento
                "\xD3" => "Ó", // O con acento
                "\xDA" => "Ú", // U con acento

                // Letra Ñ
                "\xD1" => "Ñ", // Ñ

                // Vocales con diéresis
                "\xDC" => "Ü", // U con diéresis
                "\xC4" => "Ä", // A con diéresis
                "\xCB" => "Ë", // E con diéresis
                "\xCF" => "Ï", // I con diéresis
                "\xD6" => "Ö", // O con diéresis

                // Versiones minúsculas - acentos
                "\xED" => "í",
                "\xE1" => "á",
                "\xE9" => "é",
                "\xF3" => "ó",
                "\xFA" => "ú",

                // Versión minúscula - ñ
                "\xF1" => "ñ",

                // Versiones minúsculas - diéresis
                "\xFC" => "ü",
                "\xE4" => "ä",
                "\xEB" => "ë",
                "\xEF" => "ï",
                "\xF6" => "ö"
            ];

            // Aplicar mapeo directo para caracteres especiales
            $converted = strtr($value, $char_map);

            // Si aún hay caracteres problemáticos, intentar conversión general
            if (mb_detect_encoding($converted, [self::UTF8], true) === false) {
                $converted = mb_convert_encoding($converted, self::UTF8, self::LATIN1);
            }

            return $converted;
        } catch (\Exception $e) {
            Log::warning("Error al convertir a UTF-8: {$e->getMessage()}", [
                'valor_original' => bin2hex($value)
            ]);
            // En caso de error, devolver el valor original
            return $value;
        }
    }

    /**
     * Convierte el valor proporcionado a codificación ISO-8859-1 (Latin1).
     *
     * @param string|null $value El valor a convertir.
     * @return string|null El valor convertido a ISO-8859-1.
     */
    public static function toLatin1(?string $value): ?string
    {
        // Retorna null o string vacío sin procesar
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            // Si ya está en Latin1, mantener como está
            if (!mb_detect_encoding($value, self::UTF8, true)) {
                return $value;
            }

            // Convertir de UTF-8 a Latin1, manejando caracteres que no existen en Latin1
            return mb_convert_encoding($value, self::LATIN1, self::UTF8);
        } catch (\Exception $e) {
            Log::warning("Error al convertir a Latin1: {$e->getMessage()}");
            return $value;
        }
    }

    /**
     * Sanitiza datos para ser seguros en JSON encode
     * Especialmente útil antes de mostrar notificaciones en Filament
     * 
     * @param mixed $data Datos a sanitizar
     * @return mixed Datos sanitizados
     */
    public static function sanitizeForJson($data)
    {
        if (is_string($data)) {
            // Convertir a UTF-8 válido si no lo es
            if (!mb_check_encoding($data, 'UTF-8')) {
                $data = self::toUtf8($data);
            }
            
            // Limpiar caracteres que pueden causar problemas en JSON
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
        }
        
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeForJson'], $data);
        }
        
        if (is_object($data)) {
            foreach (get_object_vars($data) as $property => $value) {
                $data->$property = self::sanitizeForJson($value);
            }
        }
        
        return $data;
    }

    /**
     * Valida que una cadena sea JSON-safe
     * 
     * @param string $string Cadena a validar
     * @return bool
     */
    public static function isJsonSafe(string $string): bool
    {
        return json_encode($string, JSON_UNESCAPED_UNICODE) !== false;
    }
}
