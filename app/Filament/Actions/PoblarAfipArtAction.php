<?php

namespace App\Filament\Actions;

use App\Models\AfipMapucheArt;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PoblarAfipArtAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Poblar ART')
            ->form([
                TextInput::make('periodo_fiscal')
                    ->label('Período Fiscal')
                    ->required()
                    ->placeholder('YYYYMM')
                    ->mask('999999')
                    ->minLength(6)
                    ->maxLength(6)
                    ->helperText('Ingrese el período fiscal en formato YYYYMM (ejemplo: 202403)'),
            ])
            ->action(function (array $data): void {
                $this->procesarDatos($data['periodo_fiscal']);
            })
            ->requiresConfirmation()
            ->modalHeading('Poblar tabla AFIP ART')
            ->modalDescription('¿Está seguro que desea poblar la tabla AFIP ART con los datos del período fiscal seleccionado?')
            ->modalSubmitActionLabel('Sí, poblar')
            ->modalCancelActionLabel('No, cancelar')
            ->color('warning')
            ->icon('heroicon-o-arrow-down-tray');
    }

    public static function getDefaultName(): ?string
    {
        return 'poblar_afip_art';
    }

    protected function procesarDatos(string $periodoFiscal): void
    {
        $connection = AfipMapucheArt::getMapucheConnection()->getName();
        try {
            DB::connection($connection)->beginTransaction();

            // Limpiamos la tabla destino
            DB::connection($connection)
                ->table('suc.afip_art')
                ->truncate();

            // Ejecutamos la inserción
            DB::connection($connection)->unprepared("
                INSERT INTO suc.afip_art (
                    nro_legaj, cuil, apellido_y_nombre, nacimiento,
                    sueldo, sexo, establecimiento, tarea
                )
                WITH base_cuils AS (
                    SELECT DISTINCT cuil,
                           CAST(SUBSTRING(cuil, 3, LENGTH(cuil) - 3) AS INTEGER) AS dni
                    FROM suc.afip_mapuche_sicoss
                    WHERE periodo_fiscal = '{$periodoFiscal}'
                ),
                latest_dh03 AS (
                    SELECT DISTINCT ON (nro_legaj)
                           nro_legaj, codc_categ, codc_uacad, chkstopliq
                    FROM mapuche.dh03
                    WHERE dh03.fec_baja IS NULL OR dh03.fec_baja >= mapuche.map_get_fecha_inicio_periodo()
                    ORDER BY nro_legaj, fec_alta DESC
                )
                SELECT DISTINCT ON (b.cuil)
                    d.nro_legaj,
                    b.cuil,
                    TRIM(s.apnom)::VARCHAR AS apellido_y_nombre,
                    d.fec_nacim AS nacimento,
                    s.rem_imp9::NUMERIC(15,2) AS sueldo,
                    d.tipo_sexo AS sexo,
                    TRIM(d3.codc_uacad)::VARCHAR AS establecimiento,
                    d11.codigoescalafon AS tarea
                FROM base_cuils b
                LEFT JOIN suc.afip_mapuche_sicoss s ON b.cuil = s.cuil
                    AND s.periodo_fiscal = '{$periodoFiscal}'
                LEFT JOIN mapuche.dh01 d ON b.dni = d.nro_cuil
                LEFT JOIN latest_dh03 d3 ON d.nro_legaj = d3.nro_legaj
                LEFT JOIN mapuche.dh11 d11 ON d3.codc_categ = d11.codc_categ
                ORDER BY b.cuil, s.periodo_fiscal DESC
            ");

            DB::connection($connection)->commit();

            $count = DB::connection($connection)
                ->table('suc.afip_art')
                ->count();

            Notification::make()
                ->title('Proceso completado')
                ->body("Se han procesado {$count} registros correctamente.")
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::connection($connection)->rollBack();

            Notification::make()
                ->title('Error')
                ->body('Ocurrió un error al procesar los datos: ' . $e->getMessage())
                ->danger()
                ->send();

            Log::error('Error al poblar afip_art', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
