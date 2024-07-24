<?php

namespace App\app\Services;

use App\Models\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile as HttpUploadedFile;

class UploadService
{
    /**
     * Almacena un archivo subido en la carpeta y el disco especificado.
     *
     * @param \Illuminate\Http\UploadedFile $file The uploaded file.
     * @param string $folder The folder to upload the file to.
     * @param string|null $disk The disk to use for the upload, defaults to 'public'.
     * @return string The path where the file was stored.
     */
    public static function uploadFile(HttpUploadedFile $file, string $folder, $disk = 'public'): string
    {
        //filename without extension
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        //extension
        $extension = $file->getClientOriginalExtension();

        //filename to store
        $fileNameToStore= $filename.'_'. time().'.'.$extension;
        //upload the file
        return $file->storeAs($folder, $fileNameToStore, $disk);
    }

    /**
     * Borra un archivo del disco especificado.
     *
     * @param string $path La ruta del archivo a eliminar.
     * @param string $disk The disk to use for the deletion, defaults to 'public'.
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
}
