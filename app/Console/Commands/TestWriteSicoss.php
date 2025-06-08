<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestWriteSicoss extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-write-sicoss';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directorio = storage_path('comunicacion/sicoss');
        if (!file_exists($directorio)) {
            if (mkdir($directorio, 0775, true)) {
                $this->info("Directorio creado: $directorio");
            } else {
                $this->error("No se pudo crear el directorio: $directorio");
                return;
            }
        } else {
            $this->info("El directorio ya existe: $directorio");
        }

        $archivo = $directorio . '/test_sicoss.txt';
        $contenido = "Prueba de escritura en " . now() . "\n";
        $fh = @fopen($archivo, 'w');
        if (!$fh) {
            $error = error_get_last();
            $this->error("No se pudo abrir el archivo: $archivo");
            $this->error("Error: " . $error['message']);
            return;
        }

        fwrite($fh, $contenido);
        fclose($fh);

        $this->info("Archivo escrito correctamente: $archivo");
    }
}
