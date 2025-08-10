<?php

namespace App\Services;

use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadService
{
    private const DEFAULT_DISK = 'public';

    /**
     * Almacena un archivo subido en la carpeta y el disco especificado.
     *
     * @param \Illuminate\Http\UploadedFile $file The uploaded file.
     * @param string $folder The folder to upload the file to.
     * @param string|null $disk The disk to use for the upload, defaults to 'public'.
     *
     * @return string The path where the file was stored.
     */
    public static function uploadFile(HttpUploadedFile $file, string $folder, $disk = self::DEFAULT_DISK): string
    {
        static::validateFolders($folder);


        //filename without extension
        $filename = pathinfo($file->getClientOriginalName(), \PATHINFO_FILENAME);

        //extension
        $extension = $file->getClientOriginalExtension();

        //filename to store
        $fileNameToStore = $filename . '_' . time() . '.' . $extension;
        // limpiar fileName
        $fileNameLimpio = static::sanitizeFileName($fileNameToStore);
        //upload the file

        try {
            Log::info('Subiendo archivo: ' . $fileNameLimpio);
            return $file->storeAs($folder, $fileNameLimpio, $disk);
        } catch (\Exception $e) {
            Log::error('Error al subir el archivo: ' . $e->getMessage());
            throw new \Exception('Error al subir el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Borra un archivo del disco especificado.
     *
     * @param string $path La ruta del archivo a eliminar.
     * @param string $disk The disk to use for the deletion, defaults to 'public'.
     *
     * @return bool True if the file was deleted successfully, false otherwise.
     */
    public static function deleteFile(string $path, string $disk = 'public'): bool
    {
        if (!Storage::disk($disk)->exists($path)) {
            return false;
        }
        return Storage::disk($disk)->delete($path);
    }

    public static function url(string $path, string $disk = 'public'): string
    {
        return Storage::disk($disk)->url($path);
    }

    /** Valida el nombre de la carpeta especificada para garantizar que solo contenga caracteres permitidos.
     *
     * @param string $folder El nombre de la carpeta a validar.
     *
     * @throws \InvalidArgumentException Si el nombre de la carpeta contiene caracteres no permitidos.
     */
    private static function validateFolders(string $folder): void
    {
        if (!preg_match('/^[a-zA-Z0-9\/\-_]+$/', $folder)) {
            throw new \InvalidArgumentException('La carpeta contiene caracteres no permitidos.');
        }
    }

    /** Limpiar un nombre de archivo reemplazando cualquier carácter no alfanumérico, sin guión, sin punto y sin guión por un guión bajo.
     *
     * @param string $fileName El nombre del archivo a limpiar.
     *
     * @return string El nombre del archivo limpio.
     */
    private static function sanitizeFileName(string $fileName): string
    {
        return preg_replace('/[^a-zA-Z0-9_.-]/', '_', $fileName);
    }
}
