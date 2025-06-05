<?php

namespace App\Repositories\Sicoss;

use App\Models\Mapuche\MapucheConfig;
use App\Repositories\Sicoss\Contracts\SicossConfigurationRepositoryInterface;

class SicossConfigurationRepository implements SicossConfigurationRepositoryInterface
{
    protected static $aportes_voluntarios;
    protected static $codigo_os_aporte_adicional;
    protected static $codigo_obrasocial_fc;
    protected static $codigo_obra_social_default;
    protected static $hs_extras_por_novedad;
    protected static $tipoEmpresa;
    protected static $asignacion_familiar;
    protected static $trabajadorConvencionado;
    protected static $codc_reparto;
    protected static $porc_aporte_adicional_jubilacion;
    protected static $cantidad_adherentes_sicoss;
    protected static $archivos;
    protected static $categoria_diferencial;

    /**
     * Carga todas las configuraciones necesarias para SICOSS
     * Código extraído tal como está del método genera_sicoss()
     */
    public function cargarConfiguraciones(): void
    {
        // Seteo valores de rrhhini
        self::$codigo_obra_social_default = MapucheConfig::getDefaultsObraSocial();
        self::$aportes_voluntarios        = MapucheConfig::getTopesJubilacionVoluntario();
        self::$codigo_os_aporte_adicional = MapucheConfig::getConceptosObraSocialAporteAdicional();
        self::$codigo_obrasocial_fc       = MapucheConfig::getConceptosObraSocialFliarAdherente();                   // concepto seteado en rrhhini bajo el cual se liquida el familiar a cargo
        self::$tipoEmpresa                = MapucheConfig::getDatosUniversidadTipoEmpresa();
        self::$cantidad_adherentes_sicoss = MapucheConfig::getConceptosInformarAdherentesSicoss();                   // Según sea cero o uno informa datos de dh09 o se fija si existe un cpncepto liquidado bajo el concepto de codigo_obrasocial_fc
        self::$asignacion_familiar        = MapucheConfig::getConceptosAcumularAsigFamiliar();                 // Si es uno se acumulan las asiganciones familiares en Asignacion Familiar en Remuneración Total (importe Bruto no imponible)
        self::$trabajadorConvencionado    = MapucheConfig::getDatosUniversidadTrabajadorConvencionado();
        self::$codc_reparto                     = MapucheConfig::getDatosCodcReparto();
        self::$porc_aporte_adicional_jubilacion = MapucheConfig::getPorcentajeAporteDiferencialJubilacion();
        self::$hs_extras_por_novedad      = MapucheConfig::getSicossHorasExtrasNovedades();   // Lee el valor HorasExtrasNovedades de RHHINI que determina si es verdadero se suman los valores de las novedades y no el importe.
        self::$categoria_diferencial       = MapucheConfig::getCategoriasDiferencial(); //obtengo las categorias seleccionadas en configuracion
    }

    /**
     * Obtiene el período fiscal actual (mes y año)
     * Código extraído tal como está del método genera_sicoss()
     */
    public function obtenerPeriodoFiscal(): array
    {
        // Se necesita filtrar datos del periodo vigente
        $per_mesct     = MapucheConfig::getMesFiscal();
        $per_anoct     = MapucheConfig::getAnioFiscal();

        return [
            'mes' => $per_mesct,
            'ano' => $per_anoct
        ];
    }

    /**
     * Genera los filtros básicos WHERE para consultas
     * Código extraído tal como está del método genera_sicoss()
     */
    public function generarFiltrosBasicos(array $datos): array
    {
        $opcion_retro  = $datos['check_retro'];
        if (isset($datos['nro_legaj'])) {
            $filtro_legajo = $datos['nro_legaj'];
        }

        // Si no filtro por número de legajo => obtengo todos los legajos
        $where = ' true ';
        if (!empty($filtro_legajo))
            $where = 'dh01.nro_legaj= ' . $filtro_legajo . ' ';

        $where_periodo = ' true ';

        return [
            'opcion_retro' => $opcion_retro,
            'filtro_legajo' => $filtro_legajo ?? null,
            'where' => $where,
            'where_periodo' => $where_periodo
        ];
    }

    /**
     * Inicializa la configuración de archivos y paths para SICOSS
     * Código extraído tal como está del método genera_sicoss()
     */
    public function inicializarConfiguracionArchivos(): array
    {
        $path = storage_path('app/comunicacion/sicoss/');
        self::$archivos = array();
        $totales = array();

        return [
            'path' => $path,
            'archivos' => self::$archivos,
            'totales' => $totales
        ];
    }

    /**
     * Obtiene el porcentaje de aporte adicional de jubilación
     */
    public function getPorcentajeAporteAdicionalJubilacion(): float
    {
        return self::$porc_aporte_adicional_jubilacion;
    }

    /**
     * Obtiene si el trabajador es convencionado
     */
    public function getTrabajadorConvencionado(): int
    {
        return self::$trabajadorConvencionado;
    }

    /**
     * Obtiene si se deben considerar asignaciones familiares
     */
    public function getAsignacionFamiliar(): bool
    {
        return self::$asignacion_familiar;
    }

    /**
     * Obtiene la cantidad de adherentes SICOSS
     */
    public function getCantidadAdherentesSicoss(): int
    {
        return self::$cantidad_adherentes_sicoss;
    }

    /**
     * Obtiene si las horas extras son por novedad
     */
    public function getHorasExtrasPorNovedad(): int
    {
        return self::$hs_extras_por_novedad;
    }

    /**
     * Obtiene el código de obra social aporte adicional
     */
    public function getCodigoObraSocialAporteAdicional(): int
    {
        return self::$codigo_os_aporte_adicional;
    }

    /**
     * Obtiene el código de aportes voluntarios
     */
    public function getAportesVoluntarios(): int
    {
        return self::$aportes_voluntarios;
    }

    /**
     * Obtiene el código de obra social familiar a cargo
     */
    public function getCodigoObraSocialFamiliarCargo(): int
    {
        return self::$codigo_obrasocial_fc;
    }
}
