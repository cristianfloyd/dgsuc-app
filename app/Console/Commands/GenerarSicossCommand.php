<?php

namespace App\Console\Commands;

use App\Scripts\GenerarSicossLegajo;
use Illuminate\Console\Command;

/**
 * Comando Artisan simple para generar archivos SICOSS.
 */
class GenerarSicossCommand extends Command
{
    /**
     * Firma del comando - legajo es opcional.
     */
    protected $signature = 'sicoss:generar {legajo?}';

    /**
     * Descripción del comando.
     */
    protected $description = 'Genera un archivo SICOSS para un legajo específico o todos los legajos';

    /**
     * Ejecuta el comando.
     */
    public function handle(): void
    {
        // Obtener el legajo (puede ser null)
        $legajo = $this->argument('legajo');

        // Convertir a entero si se proporciona
        $numeroLegajo = $legajo ? (int) $legajo : null;

        // Crear instancia del generador
        $generador = new GenerarSicossLegajo;

        // Mostrar mensaje inicial
        if ($numeroLegajo) {
            $this->info("🎯 Generando SICOSS para legajo: {$numeroLegajo}");
        } else {
            $this->info('📋 Generando SICOSS para todos los legajos');
        }

        // Ejecutar generación
        $resultado = $generador->generar($numeroLegajo);

        // Mostrar resultado
        if ($resultado['success']) {
            $this->mostrarResultadoExitoso($resultado);
        } else {
            $this->error("❌ Error: {$resultado['message']}");
        }
    }

    /**
     * Muestra el resultado exitoso con formato mejorado.
     */
    private function mostrarResultadoExitoso(array $resultado): void
    {
        $this->info('✅ SICOSS generado exitosamente');
        $this->newLine();

        // Mostrar archivos generados
        $this->info('📁 Archivos generados:');
        $this->line("   📄 Archivo: {$resultado['data']['archivo']}");
        $this->line("   📦 ZIP: {$resultado['data']['zip']}");
        $this->newLine();

        // Mostrar datos procesados según el tipo
        $datosFormateados = $resultado['data']['datos_procesados'];

        switch ($datosFormateados['tipo']) {
            case 'legajo_individual':
                $this->mostrarDatosLegajoIndividual($datosFormateados);
                break;

            case 'multiples_legajos':
                $this->mostrarResumenMultiplesLegajos($datosFormateados);
                break;

            case 'sin_datos':
                $this->warn("⚠️  {$datosFormateados['mensaje']}");
                break;
        }
    }

    /**
     * Muestra los datos de un legajo individual.
     */
    private function mostrarDatosLegajoIndividual(array $datos): void
    {
        $info = $datos['informacion_basica'];
        $importes = $datos['datos_completos'];
        dd($importes);
        // Información básica del empleado
        $this->info('👤 Información del Empleado:');
        $this->line("   • Legajo: {$info['legajo']}");
        $this->line("   • CUIT: {$info['cuit']}");
        $this->line("   • Nombre: {$info['apellido_nombres']}");
        $this->line("   • Estado: {$info['estado']}");
        $this->line("   • Días Trabajados: {$info['dias_trabajados']}");
        $this->newLine();

        // Importes principales
        $this->info('💰 Importes SICOSS:');
        $this->line("   • BRUTO:        $ {$importes['importeimponible_9']}");

        // Solo mostrar imponibles que no sean 0
        $imponiblesRelevantes = [
            'IMPONIBLE 1' => $importes['IMPORTE_IMPON'],
            'IMPONIBLE 2' => $importes['ImporteImponiblePatronal'],
            'IMPONIBLE 3' => $importes['ImporteImponiblePatronal'],
            'IMPONIBLE 4' => $importes['ImporteImponible_4'],
            'IMPONIBLE 5' => $importes['ImporteImponible_4'] - $importes['ImporteTipo91'],
            'IMPONIBLE 6' => $importes['ImporteImponible_6'],
            'IMPONIBLE 7' => $importes['ImporteImponible_6'],
            'IMPONIBLE 8' => $importes['Remuner78805'],
            'IMPONIBLE 9' => $importes['importeimponible_9'],
        ];

        foreach ($imponiblesRelevantes as $concepto => $valor) {
            $this->line("   • {$concepto}: $ {$valor}");
        }
    }

    /**
     * Muestra el resumen de múltiples legajos.
     */
    private function mostrarResumenMultiplesLegajos(array $datos): void
    {
        $resumen = $datos['resumen'];

        // Resumen general
        $this->info('📊 Resumen General:');
        $this->line("   • Total Legajos: {$resumen['total_legajos']}");
        $this->line("   • Total Bruto: $ {$resumen['total_bruto']}");
        $this->line("   • Total Imponible: $ {$resumen['total_imponible']}");
        $this->newLine();

        // Mostrar algunos legajos como muestra (máximo 10)
        $legajosMuestra = \array_slice($datos['legajos'], 0, 10);

        $this->info('👥 Legajos Procesados (muestra):');
        foreach ($legajosMuestra as $legajo) {
            $this->line("   • {$legajo['legajo']} - {$legajo['apellido_nombres']} - Bruto: $ {$legajo['bruto']}");
        }

        if (\count($datos['legajos']) > 10) {
            $restantes = \count($datos['legajos']) - 10;
            $this->line("   ... y {$restantes} legajos más");
        }
    }
}
