<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


/**
 * Class FileUploadService
 *
 * Este servicio maneja operaciones de carga de archivos, incluyendo la eliminación de archivos.
 */
class FileUploadService
{


    /**
     * Borra un archivo por su filepath.
     *
     * @param string $filePath El filepath del archivo a borrar.
     * @return bool Devuelve true si el archivo se borró correctamente, false en caso contrario.
     */
    public function deleteFile(string $filePath): bool
    {
        try {
            // Check if the file exists
            if (!Storage::exists($filePath)) {
                Log::warning("File not found: {$filePath}");
                return false;
            }

            // Attempt to delete the file
            $deleted = Storage::delete($filePath);

            if ($deleted) {
                Log::info("File successfully deleted: {$filePath}");
            } else {
                Log::error("Failed to delete file: {$filePath}");
            }

            return $deleted;
        } catch (\Exception $e) {
            Log::error("Error deleting file {$filePath}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sube un archivo al servidor.
     *
     * @param \Illuminate\Http\UploadedFile $file El archivo que se va a subir
     * @param string $path La ruta donde se debe almacenar el archivo
     * @return string|false La ruta del archivo subido si es exitoso, falso en caso contrario
     */
    public function uploadFile(UploadedFile $file, string $path)
    {


        try {
            $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $timenow = time();
            $filename = "{$filename}-{$timenow}.{$extension}";

            $filePath = Storage::putFileAs($path, $file, $filename);

            if ($filePath) {
                Log::info("File successfully uploaded: {$filePath}");
                return $filePath;
            } else {
                Log::error("Failed to upload file");
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Error uploading file: " . $e->getMessage());
            return false;
        }
    }
}
