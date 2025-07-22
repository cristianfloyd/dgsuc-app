<?php

namespace App\Repositories\Sicoss;

use App\Data\Sicoss\SicossProcessData;
use App\Models\Dh01;
use App\Models\Dh03;
use App\Models\Dh11;
use App\Models\Mapuche\MapucheConfig;
use App\Repositories\Sicoss\Contracts\Dh03RepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossCalculoRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossConfigurationRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossEstadoRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossFormateadorRepositoryInterface;
use App\Repositories\Sicoss\Contracts\SicossLegajoProcessorRepositoryInterface;
use App\Services\Mapuche\LicenciaService;
use App\Traits\MapucheConnectionTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SicossLegajoProcessorRepository implements SicossLegajoProcessorRepositoryInterface
{
    use MapucheConnectionTrait;

    public function __construct(
        protected Dh03RepositoryInterface $dh03Repository,
        protected SicossCalculoRepositoryInterface $sicossCalculoRepository,
        protected SicossEstadoRepositoryInterface $sicossEstadoRepository,
        protected SicossFormateadorRepositoryInterface $sicossFormateadorRepository,
        protected SicossConfigurationRepositoryInterface $sicossConfigurationRepository,
    ) {
    }

    /**
     * Procesa los legajos para el cálculo de SICOSS.
     *
     * @param SicossProcessData $datos Datos de configuración para el procesamiento
     * @param int $per_anoct Año de la liquidación
     * @param int $per_mesct Mes de la liquidación
     * @param array $legajos Array con los legajos a procesar
     * @param string $nombre_arch Nombre del archivo de salida
     * @param array|null $licencias Array de licencias (opcional)
     * @param bool $retro Indica si es una liquidación retroactiva
     * @param bool $check_sin_activo Verifica legajos sin activos
     * @param bool $retornar_datos Indica si debe retornar los datos procesados
     *
     * @return array Array con los resultados del procesamiento
     */
    public function procesarSicoss(
        SicossProcessData $datos,
        int $per_anoct,
        int $per_mesct,
        array $legajos,
        string $nombre_arch,
        ?array $licencias = null,
        bool $retro = false,
        bool $check_sin_activo = false,
        bool $retornar_datos = false,
    ): array {
        // Convertir objetos stdClass a arrays si es necesario
        $legajos = array_map(function ($legajo) {
            return \is_object($legajo) ? (array)$legajo : $legajo;
        }, $legajos);

        // Usar los valores del DTO directamente
        $TopeJubilatorioPatronal = $datos->TopeJubilatorioPatronal;
        $TopeJubilatorioPersonal = $datos->TopeJubilatorioPersonal;
        $TopeOtrosAportesPersonales = $datos->TopeOtrosAportesPersonal;
        $trunca_tope = $datos->truncaTope;

        // Calcular los topes SAC
        $TopeSACJubilatorioPers = $TopeJubilatorioPersonal / 2;
        $TopeSACJubilatorioPatr = $TopeJubilatorioPatronal / 2;
        $TopeSACJubilatorioOtroAp = $TopeOtrosAportesPersonales / 2;

        // Inicializo para guardar el total de cada tipo de importe para luego mostrar en informe de control
        $total = [];
        $total['bruto'] = 0;
        $total['imponible_1'] = 0;
        $total['imponible_2'] = 0;
        $total['imponible_4'] = 0;
        $total['imponible_5'] = 0;
        $total['imponible_6'] = 0; //bruto + sac docente
        $total['imponible_8'] = 0;
        $total['imponible_9'] = 0;
        $legajos_validos = [];
        $j = 0;

        // En este for se completan los campos necesarios para cada uno de los legajos liquidados
        for ($i = 0; $i < \count(array_values($legajos)); $i++) {
            $legajo = $legajos[$i]['nro_legaj'];

            $legajos[$i]['ImporteSACOtroAporte'] = 0;
            $legajos[$i]['TipoDeOperacion'] = 0;
            $legajos[$i]['ImporteImponible_4'] = 0;
            $legajos[$i]['ImporteSACNoDocente'] = 0;

            $legajos[$i]['ImporteSACDoce'] = 0;
            $legajos[$i]['ImporteSACAuto'] = 0;

            $legajos[$i]['codigo_os'] = $this->sicossCalculoRepository->codigoOs($legajo);

            //#44909 Incorporar a la salida de SICOSS el código de situación Reserva de Puesto (14)
            if ($check_sin_activo) {
                $legajo_sin_liquidar = Dh01::getLegajoSinLiquidarYSinDh21($legajo);

                if ($legajo_sin_liquidar) {
                    $legajos[$i]['codigosituacion'] = 14;
                }
            }

            if (!$retro) {
                $dh03Repository = app(Dh03RepositoryInterface::class);
                $limites = $dh03Repository->getLimitesCargos($legajo);

                // Convertir objetos stdClass a arrays si es necesario
                $limites = array_map(function ($limite) {
                    return \is_object($limite) ? (array)$limite : $limite;
                }, $limites);

                //En caso de que el agente no tenga cargos activos, pero aparezca liquidado.
                if (!isset($limites[0]['maximo'])) {
                    $cargos_activos_agente = Dh03::getCargosActivos($legajo);
                    if (empty($cargos_activos_agente)) {
                        $fecha_fin = MapucheConfig::getFechaFinPeriodoCorriente();
                        $limites[0]['maximo'] = substr($fecha_fin, 9, 2);
                    }
                }
                $estado_situacion = $this->sicossEstadoRepository->inicializarEstadoSituacion($legajos[$i]['codigosituacion'], $limites[0]['minimo'], $limites[0]['maximo']);
                $cargos_legajo = $dh03Repository->getCargosActivosSinLicencia($legajo);
                $cargos_legajo2 = $dh03Repository->getCargosActivosConLicenciaVigente($legajo);
                $cargos_legajo = array_merge($cargos_legajo, $cargos_legajo2);

                // Convertir objetos stdClass a arrays si es necesario
                $cargos_legajo = array_map(function ($cargo) {
                    return \is_object($cargo) ? (array)$cargo : $cargo;
                }, $cargos_legajo);
                // En el caso de las licencias de legajo, se mantiene el código de condición en esos días
                // que corresponde al tipo de licencia (5 => maternidad o 13 => no remunerada)
                // Se considera que no se puede superponer con otra licencia
                $dias_lic_legajo = [];

                // Se evaluan las licencias
                if ($licencias != null) {

                    foreach ($licencias as $licencia) {
                        if ($licencia['nro_legaj'] == $legajo) {
                            for ($dia = $licencia['inicio']; $dia <= $licencia['final']; $dia++) {
                                if (!\in_array($dia, $dias_lic_legajo)) { // Los días con licencia de legajo no se tocan
                                    if ($limites[0]['maximo'] >= $dia) {
                                        $estado_situacion[$dia] = $this->sicossEstadoRepository->evaluarCondicionLicencia($estado_situacion[$dia], $licencia['condicion']);
                                    }
                                    if ($licencia['es_legajo']) {
                                        $dias_lic_legajo[] = $dia; // En este día cuenta con licencia de legajo
                                    }
                                }
                            }
                        }
                    }
                }

                $licencias_cargos = [];
                foreach ($cargos_legajo as $cargo) {
                    $fin_mes = $day = date('d', mktime(0, 0, 0, MapucheConfig::getMesFiscal() + 1, 0, date('Y')));
                    for ($ini_mes = 1; $ini_mes <= $fin_mes; $ini_mes++) {
                        if (!isset($licencias_cargos[$cargo['nro_cargo']][$i])) {
                            $licencias_cargos[$cargo['nro_cargo']][$ini_mes] = 1;
                        }

                        if ((isset($cargo['inicio_lic'], $cargo['final_lic'])) && $ini_mes >= $cargo['inicio_lic'] && $ini_mes <= $cargo['final_lic']) {
                            $licencias_cargos[$cargo['nro_cargo']][$ini_mes] = $cargo['condicion'];
                        } else {
                            $licencias_cargos[$cargo['nro_cargo']][$ini_mes] = 1;
                        }
                    }
                }

                // Se evaluan los cargos
                foreach ($licencias_cargos as $cargo) {
                    for ($dia = 1; $dia <= \count($cargo); $dia++) {
                        if (!\in_array($dia, $dias_lic_legajo)) {
                            if ((isset($estado_situacion[$dia]) && $estado_situacion[$dia] == 13)) {
                                $estado_situacion[$dia] = $cargo[$dia]; // Si estaba trabajando en algún cargo se prioriza el código en dha8
                            }
                        }
                    }
                }

                $cambios_estado = $this->sicossEstadoRepository->calcularCambiosEstado($estado_situacion);
                $dias_trabajados = $this->sicossEstadoRepository->calcularDiasTrabajados($estado_situacion);
                $revista_legajo = $this->sicossEstadoRepository->calcularRevistaLegajo($cambios_estado);


                // Como código de situación general se toma el último (?)
                $legajos[$i]['codigosituacion'] = $estado_situacion[$limites[0]['maximo']];
                // Revista 1
                $legajos[$i]['codigorevista1'] = $revista_legajo[1]['codigo'];
                $legajos[$i]['fecharevista1'] = $revista_legajo[1]['dia'];
                // Revista 2
                if ($revista_legajo[2]['codigo'] == 0) {
                    $legajos[$i]['codigorevista2'] = $revista_legajo[1]['codigo'];
                } else {
                    $legajos[$i]['codigorevista2'] = $revista_legajo[2]['codigo'];
                }
                $legajos[$i]['fecharevista2'] = $revista_legajo[2]['dia'];

                // Revista 3
                if ($revista_legajo[3]['codigo'] == 0) {
                    $legajos[$i]['codigorevista3'] = $legajos[$i]['codigorevista2'];
                } else {
                    $legajos[$i]['codigorevista3'] = $revista_legajo[3]['codigo'];
                }
                $legajos[$i]['fecharevista3'] = $revista_legajo[3]['dia'];

                // Como días trabajados se toman aquellos días de cargo menos los días de licencia sin goce (?)
                $legajos[$i]['dias_trabajados'] = $dias_trabajados;
            } else {
                // Se evaluan

                // Si tiene una licencia por maternidad activa el codigo de situacion es 5
                if (LicenciaService::tieneLicenciaMaternidadActiva($legajo)) {
                    $legajos[$i]['codigosituacion'] = 5;
                }

                // Si tengo chequeado el tilde de licencias cambio el codigo de situacion y la cantidad de dias trabajados se vuelve 0
                if ($datos->check_lic && ($legajos[$i]['licencia'] == 1)) {
                    $legajos[$i]['codigosituacion'] = 13;
                    $legajos[$i]['dias_trabajados'] = '00';
                } else {
                    $legajos[$i]['dias_trabajados'] = '30';
                }

                $legajos[$i]['codigorevista1'] = $legajos[$i]['codigosituacion'];
                $legajos[$i]['fecharevista1'] = '01';
                $legajos[$i]['codigorevista2'] = '00';
                $legajos[$i]['fecharevista2'] = '00';
                $legajos[$i]['codigorevista3'] = '00';
                $legajos[$i]['fecharevista3'] = '00';
            }

            // Se informa solo si tiene conyugue o no; no la cantidad
            if ($legajos[$i]['conyugue'] > 0) {
                $legajos[$i]['conyugue'] = 1;
            }


            // --- Obtengo la sumarización según concepto o tipo de grupo de un concepto ---
            $this->sumarizarConceptosPorTiposGrupos($legajo, $legajos[$i]);

            // --- Otros datos remunerativos ---

            // Sumarizar conceptos segun tipo de concepto
            $suma_conceptos_tipoC = $this->sicossCalculoRepository->calcularRemunerGrupo($legajo, 'C', 'nro_orimp >0 AND codn_conce > 0');
            $suma_conceptos_tipoF = $this->sicossCalculoRepository->calcularRemunerGrupo($legajo, 'F', 'true');

            $legajos[$i]['Remuner78805'] = $suma_conceptos_tipoC;
            $legajos[$i]['AsignacionesFliaresPagadas'] = $suma_conceptos_tipoF;
            $legajos[$i]['ImporteImponiblePatronal'] = $suma_conceptos_tipoC;

            // Para calcular Remuneracion total= IMPORTE_BRUTO
            $legajos[$i]['DiferenciaSACImponibleConTope'] = 0;
            $legajos[$i]['DiferenciaImponibleConTope'] = 0;
            $legajos[$i]['ImporteSACPatronal'] = $legajos[$i]['ImporteSAC'];
            $legajos[$i]['ImporteImponibleSinSAC'] = $legajos[$i]['ImporteImponiblePatronal'] - $legajos[$i]['ImporteSACPatronal'];
            if ($legajos[$i]['ImporteSAC'] > $TopeSACJubilatorioPatr && $trunca_tope == 1) {
                $legajos[$i]['DiferenciaSACImponibleConTope'] = $legajos[$i]['ImporteSAC'] - $TopeSACJubilatorioPatr;
                $legajos[$i]['ImporteImponiblePatronal'] -= $legajos[$i]['DiferenciaSACImponibleConTope'];
                $legajos[$i]['ImporteSACPatronal'] = $TopeSACJubilatorioPatr;
            }

            if ($legajos[$i]['ImporteImponibleSinSAC'] > $TopeJubilatorioPatronal && $trunca_tope == 1) {
                $legajos[$i]['DiferenciaImponibleConTope'] = $legajos[$i]['ImporteImponibleSinSAC'] - $TopeJubilatorioPatronal;
                $legajos[$i]['ImporteImponiblePatronal'] -= $legajos[$i]['DiferenciaImponibleConTope'];
            }

            $legajos[$i]['IMPORTE_BRUTO'] = $legajos[$i]['ImporteImponiblePatronal'] + $legajos[$i]['ImporteNoRemun'];

            // Para calcular IMPORTE_IMPON que es lo mismo que importe imponible 1
            $legajos[$i]['IMPORTE_IMPON'] = 0;
            $legajos[$i]['IMPORTE_IMPON'] = $suma_conceptos_tipoC;

            $VerificarAgenteImportesCERO = 1;

            // Si es el check de informar becarios en configuracion esta chequeado entonces sumo al importe imponible la suma de conceptos de ese tipo de grupo (Becarios ART)
            if ($legajos[$i]['ImporteImponibleBecario'] != 0) {
                $legajos[$i]['IMPORTE_IMPON'] += $legajos[$i]['ImporteImponibleBecario'];
                $legajos[$i]['IMPORTE_BRUTO'] += $legajos[$i]['ImporteImponibleBecario'];
                $legajos[$i]['ImporteImponiblePatronal'] += $legajos[$i]['ImporteImponibleBecario'];
                $legajos[$i]['Remuner78805'] += $legajos[$i]['ImporteImponibleBecario'];
            }

            if ($this->sicossEstadoRepository->verificarAgenteImportesCero($legajos[$i]) == 1 || $legajos[$i]['codigosituacion'] == 5 || $legajos[$i]['codigosituacion'] == 11) { // codigosituacion=5 y codigosituacion=11 quiere decir maternidad y debe infrormarse
                $legajos[$i]['PorcAporteDiferencialJubilacion'] = $this->sicossConfigurationRepository->getPorcentajeAporteAdicionalJubilacion();
                $legajos[$i]['ImporteImponible_4'] = $legajos[$i]['IMPORTE_IMPON'];
                $legajos[$i]['ImporteSACNoDocente'] = 0;
                //ImporteImponible_6 viene con valor de funcion sumarizar_conceptos_por_tipos_grupos
                $legajos[$i]['ImporteImponible_6'] = round((($legajos[$i]['ImporteImponible_6'] * 100) / $legajos[$i]['PorcAporteDiferencialJubilacion']), 2);
                $Imponible6_aux = $legajos[$i]['ImporteImponible_6'];
                if ($Imponible6_aux != 0) {
                    if (
                        (int)$Imponible6_aux != (int)$legajos[$i]['IMPORTE_IMPON']
                        && (abs($Imponible6_aux - $legajos[$i]['IMPORTE_IMPON'])) > 5 //redondear hasta + o - $5
                        && $legajos[$i]['ImporteImponible_6'] < $legajos[$i]['IMPORTE_IMPON']
                    ) {
                        $legajos[$i]['TipoDeOperacion'] = 2;
                        $legajos[$i]['IMPORTE_IMPON'] = $legajos[$i]['IMPORTE_IMPON'] - $legajos[$i]['ImporteImponible_6'];
                        $legajos[$i]['ImporteSACNoDocente'] = $legajos[$i]['ImporteSAC'] - $legajos[$i]['SACInvestigador'];
                    } else {
                        if ((($Imponible6_aux + 5) > $legajos[$i]['IMPORTE_IMPON'])
                            && (($Imponible6_aux - 5) < $legajos[$i]['IMPORTE_IMPON'])
                        ) {
                            $legajos[$i]['ImporteImponible_6'] = $legajos[$i]['IMPORTE_IMPON'];
                        } else {
                            $legajos[$i]['ImporteImponible_6'] = $Imponible6_aux;
                        }
                        $legajos[$i]['TipoDeOperacion'] = 1;
                        $legajos[$i]['ImporteSACNoDocente'] = $legajos[$i]['ImporteSAC'];
                    }
                } else {
                    $legajos[$i]['TipoDeOperacion'] = 1;
                    $legajos[$i]['ImporteSACNoDocente'] = $legajos[$i]['ImporteSAC'];
                }

                $legajos[$i]['ImporteSACOtroAporte'] = $legajos[$i]['ImporteSAC'];
                $legajos[$i]['DiferenciaSACImponibleConTope'] = 0;
                $legajos[$i]['DiferenciaImponibleConTope'] = 0;



                $tope_jubil_personal = $TopeJubilatorioPersonal;
                if ($legajos[$i]['ImporteSAC'] > 0) {
                    $tope_jubil_personal = $TopeJubilatorioPersonal + $TopeSACJubilatorioPers;
                }


                if ($legajos[$i]['ImporteSACNoDocente'] > $tope_jubil_personal) {
                    if ($trunca_tope == 1) {
                        $legajos[$i]['DiferenciaSACImponibleConTope'] = $legajos[$i]['ImporteSACNoDocente'] - $TopeSACJubilatorioPers;
                        $legajos[$i]['IMPORTE_IMPON'] -= $legajos[$i]['DiferenciaSACImponibleConTope'];
                        $legajos[$i]['ImporteSACNoDocente'] = $TopeSACJubilatorioPers;
                    }
                } else {

                    if ($trunca_tope == 1) {

                        $bruto_nodo_sin_sac = $legajos[$i]['IMPORTE_BRUTO'] - $legajos[$i]['ImporteImponible_6'] - $legajos[$i]['ImporteSACNoDocente'];

                        $sac = $legajos[$i]['ImporteSACNoDocente'];

                        $tope = min($bruto_nodo_sin_sac, $TopeJubilatorioPersonal) + min($sac, $TopeSACJubilatorioPers);
                        $imp_1 = $legajos[$i]['IMPORTE_BRUTO'] - $legajos[$i]['ImporteImponible_6'];

                        $tope_sueldo = min($bruto_nodo_sin_sac - $legajos[$i]['ImporteNoRemun'], $TopeJubilatorioPersonal);
                        $tope_sac = min($sac, $TopeSACJubilatorioPers);


                        $legajos[$i]['IMPORTE_IMPON'] = min($bruto_nodo_sin_sac - $legajos[$i]['ImporteNoRemun'], $TopeJubilatorioPersonal) + min($sac, $TopeSACJubilatorioPers);
                    }
                }

                $explode = explode(',', self::$categoria_diferencial ?? ''); //arma el array
                $implode = implode("','", $explode); //vulve a String y agrega comillas
                if (Dh11::existeCategoriaDiferencial($legajos[$i]['nro_legaj'], $implode)) {
                    $legajos[$i]['IMPORTE_IMPON'] = 0;
                }

                $legajos[$i]['ImporteImponibleSinSAC'] = $legajos[$i]['IMPORTE_IMPON'] - $legajos[$i]['ImporteSACNoDocente'];


                $tope_jubil_personal = $TopeJubilatorioPersonal;
                if ($legajos[$i]['ImporteSAC'] > 0) {
                    $tope_jubil_personal = $TopeJubilatorioPersonal + $TopeSACJubilatorioPers;
                } else {
                    $tope_jubil_personal = $TopeJubilatorioPersonal;
                }

                if ($legajos[$i]['ImporteImponibleSinSAC'] > $tope_jubil_personal) {
                    if ($trunca_tope == 1) {
                        $legajos[$i]['DiferenciaImponibleConTope'] = $legajos[$i]['ImporteImponibleSinSAC'] - $TopeJubilatorioPersonal;
                        $legajos[$i]['IMPORTE_IMPON'] -= $legajos[$i]['DiferenciaImponibleConTope'];
                    }
                }


                $otra_actividad = $this->sicossCalculoRepository->otraActividad($legajo);
                $legajos[$i]['ImporteBrutoOtraActividad'] = $otra_actividad['importebrutootraactividad'];
                $legajos[$i]['ImporteSACOtraActividad'] = $otra_actividad['importesacotraactividad'];

                if (($legajos[$i]['ImporteBrutoOtraActividad'] != 0) || ($legajos[$i]['ImporteSACOtraActividad'] != 0)) {
                    if (($legajos[$i]['ImporteBrutoOtraActividad'] + $legajos[$i]['ImporteSACOtraActividad']) >= ($TopeSACJubilatorioPers + $TopeJubilatorioPatronal)) {
                        $legajos[$i]['IMPORTE_IMPON'] = 0.00;
                    } else {
                        $imp_1_tope = 0.0;
                        $imp_1_tope_sac = 0.0;

                        if ($TopeJubilatorioPersonal > $legajos[$i]['ImporteBrutoOtraActividad']) {
                            $imp_1_tope += $TopeJubilatorioPersonal - $legajos[$i]['ImporteBrutoOtraActividad'];
                        }

                        if ($TopeSACJubilatorioPers > $legajos[$i]['ImporteSACOtraActividad']) {
                            $imp_1_tope_sac += $TopeSACJubilatorioPers - $legajos[$i]['ImporteSACOtraActividad'];
                        }

                        if ($imp_1_tope > $legajos[$i]['ImporteImponibleSinSAC']) {
                            $imp_1_tope = $legajos[$i]['ImporteImponibleSinSAC'];
                        }

                        if ($imp_1_tope_sac > $legajos[$i]['ImporteSACPatronal']) {
                            $imp_1_tope_sac = $legajos[$i]['ImporteSACPatronal'];
                        }

                        $legajos[$i]['IMPORTE_IMPON'] = $imp_1_tope_sac + $imp_1_tope;
                    }
                }

                $legajos[$i]['DifSACImponibleConOtroTope'] = 0;
                $legajos[$i]['DifImponibleConOtroTope'] = 0;
                if ($legajos[$i]['ImporteSACOtroAporte'] > $TopeSACJubilatorioOtroAp) {
                    if ($trunca_tope == 1) {
                        $legajos[$i]['DifSACImponibleConOtroTope'] = $legajos[$i]['ImporteSACOtroAporte'] - $TopeSACJubilatorioOtroAp;
                        $legajos[$i]['ImporteImponible_4'] -= $legajos[$i]['DifSACImponibleConOtroTope'];
                        $legajos[$i]['ImporteSACOtroAporte'] = $TopeSACJubilatorioOtroAp;
                    }
                }
                $legajos[$i]['OtroImporteImponibleSinSAC'] = $legajos[$i]['ImporteImponible_4'] - $legajos[$i]['ImporteSACOtroAporte'];
                if ($legajos[$i]['OtroImporteImponibleSinSAC'] > $TopeOtrosAportesPersonales) {
                    if ($trunca_tope == 1) {
                        $legajos[$i]['DifImponibleConOtroTope'] = $legajos[$i]['OtroImporteImponibleSinSAC'] - $TopeOtrosAportesPersonales;
                        $legajos[$i]['ImporteImponible_4'] -= $legajos[$i]['DifImponibleConOtroTope'];
                    }
                }
                if ($legajos[$i]['ImporteImponible_6'] != 0 && $legajos[$i]['TipoDeOperacion'] == 1) {
                    $legajos[$i]['IMPORTE_IMPON'] = 0;
                }
                // Calcular Sueldo más Adicionales
                $legajos[$i]['ImporteSueldoMasAdicionales'] = $legajos[$i]['ImporteImponiblePatronal'] -
                    $legajos[$i]['ImporteSAC'] -
                    $legajos[$i]['ImporteHorasExtras'] -
                    $legajos[$i]['ImporteZonaDesfavorable'] -
                    $legajos[$i]['ImporteVacaciones'] -
                    $legajos[$i]['ImportePremios'] -
                    $legajos[$i]['ImporteAdicionales'];
                if ($legajos[$i]['ImporteSueldoMasAdicionales'] > 0) {
                    $legajos[$i]['ImporteSueldoMasAdicionales'] -= $legajos[$i]['IncrementoSolidario'];
                }

                if ($legajos[$i]['trabajadorconvencionado'] === null) {
                    $legajos[$i]['trabajadorconvencionado'] = $this->sicossConfigurationRepository->getTrabajadorConvencionado();
                }

                // Sumariza las asiganciones familiares en el bruto y deja las asiganciones familiares en cero, esto si en configuracion esta chequeado
                if ($this->sicossConfigurationRepository->getAsignacionFamiliar()) {
                    $legajos[$i]['IMPORTE_BRUTO'] += $legajos[$i]['AsignacionesFliaresPagadas'];
                    $legajos[$i]['AsignacionesFliaresPagadas'] = 0;
                }

                // Por ticket #3947. Check "Generar ART con tope"
                if (MapucheConfig::getParametroRrhh('Sicoss', 'ARTconTope', '1') === '0') { // Sin tope
                    $legajos[$i]['importeimponible_9'] = $legajos[$i]['Remuner78805'];
                } else { // Con tope
                    $legajos[$i]['importeimponible_9'] = $legajos[$i]['ImporteImponible_4'];
                }

                // Por ticket #3947. Check "Considerar conceptos no remunerativos en cálculo de ART?"
                if (MapucheConfig::getParametroRrhh('Sicoss', 'ConceptosNoRemuEnART', '0') === '1') { // Considerar conceptos no remunerativos
                    $legajos[$i]['importeimponible_9'] += $legajos[$i]['ImporteNoRemun'];
                }

                // por GDS #5913 Incorporación de conceptos no remunerativos a las remuneraciones 4 y 8 de SICOSS
                $legajos[$i]['Remuner78805'] += $legajos[$i]['NoRemun4y8'];
                $legajos[$i]['ImporteImponible_5'] = $legajos[$i]['ImporteImponible_4'];
                $legajos[$i]['ImporteImponible_4'] += $legajos[$i]['NoRemun4y8'];
                $legajos[$i]['ImporteImponible_4'] += $legajos[$i]['ImporteTipo91'];

                $legajos[$i]['IMPORTE_BRUTO'] += $legajos[$i]['ImporteNoRemun96'];
                $total['bruto'] += round($legajos[$i]['IMPORTE_BRUTO'], 2);
                $total['imponible_1'] += round($legajos[$i]['IMPORTE_IMPON'], 2);
                $total['imponible_2'] += round($legajos[$i]['ImporteImponiblePatronal'], 2);
                $total['imponible_4'] += round($legajos[$i]['ImporteImponible_4'], 2);
                $total['imponible_5'] += round($legajos[$i]['ImporteImponible_5'], 2);
                $total['imponible_8'] += round($legajos[$i]['Remuner78805'], 2);
                $total['imponible_6'] += round($legajos[$i]['ImporteImponible_6'], 2);
                $total['imponible_9'] += round($legajos[$i]['importeimponible_9'], 2);

                $legajos_validos[$j] = $legajos[$i];
                $j++;
            } // fin else que verifica que los importes sean distintos de 0
            // Si los importes son cero el legajo no se agrega al archivo sicoss; pero cuando tengo el check de licencias por interface y ademas el legajo tiene licencias entonces si va
            elseif ($datos->check_lic && ($legajos[$i]['licencia'] == 1)) {
                // Inicializo variables faltantes en cero
                $legajos[$i]['ImporteSueldoMasAdicionales'] = 0;
                if ($legajos[$i]['trabajadorconvencionado'] === null) {
                    $legajos[$i]['trabajadorconvencionado'] = $this->sicossConfigurationRepository->getTrabajadorConvencionado();
                }

                if ($datos->seguro_vida_patronal == 1 && $datos->check_lic == 1) {
                    $legajos[$i]['SeguroVidaObligatorio'] = 1;
                }
                $legajos_validos[$j] = $legajos[$i];
                $j++;
            } elseif ($check_sin_activo && $legajos[$i]['codigosituacion'] == 14) {
                $legajos_validos[$j] = $legajos[$i];
                $j++;
            }
        }

        if (!empty($legajos_validos)) {
            if ($retornar_datos === true) {
                return $legajos_validos;
            }
            $this->grabarEnTxt($legajos_validos, $nombre_arch);
        }

        dd($total);
        return $total;
    }

    /**
     * Graba los legajos procesados en archivo TXT
     * Método auxiliar extraído de SicossLegacy.
     */
    public function grabarEnTxt(array $legajos, string $nombre_arch): void
    {
        try {
            $contenido = '';
            $totalLegajos = \count($legajos);
            $procesados = 0;

            Log::info('Iniciando grabación de archivo SICOSS TXT', [
                'archivo' => $nombre_arch,
                'legajos' => $totalLegajos,
            ]);

            foreach ($legajos as $legajo) {
                $linea = $this->generarLineaSicoss($legajo);
                $contenido .= $linea . \PHP_EOL;
                $procesados++;

                // Loguear progreso cada 100 registros
                if ($procesados % 100 === 0) {
                    Log::info("Grabación SICOSS: {$procesados}/{$totalLegajos} registros procesados");
                }
            }

            // Crear directorio si no existe
            $directorio = storage_path('app/comunicacion/sicoss/');
            if (!is_dir($directorio)) {
                mkdir($directorio, 0o755, true);
            }

            // Guardar archivo
            $rutaCompleta = $directorio . $nombre_arch . '.txt';
            file_put_contents($rutaCompleta, $contenido);

            Log::info('Archivo SICOSS grabado exitosamente', [
                'archivo' => $rutaCompleta,
                'legajos_procesados' => $procesados,
                'tamaño_archivo' => filesize($rutaCompleta) . ' bytes',
            ]);

        } catch (\Exception $e) {
            Log::error('Error al grabar archivo SICOSS TXT', [
                'archivo' => $nombre_arch,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Sumariza conceptos por tipos y grupos
     * Método auxiliar extraído de SicossLegacy.
     */
    public function sumarizarConceptosPorTiposGrupos(int $nro_leg, array &$leg): void
    {
        $leg['ImporteSAC'] = 0;
        $leg['SACPorCargo'] = 0;
        $leg['ImporteHorasExtras'] = 0;
        $leg['ImporteVacaciones'] = 0;
        $leg['ImporteRectificacionRemun'] = 0;
        $leg['ImporteAdicionales'] = 0;
        $leg['ImportePremios'] = 0;
        $leg['ImporteNoRemun'] = 0;
        $leg['ImporteMaternidad'] = 0;
        $leg['ImporteZonaDesfavorable'] = 0;
        $leg['PrioridadTipoDeActividad'] = 0;
        $leg['IMPORTE_VOLUN'] = 0;
        $leg['IMPORTE_ADICI'] = 0;
        $leg['TipoDeActividad'] = 0;
        $leg['ImporteImponible_6'] = 0;
        $leg['SACInvestigador'] = 0;
        $leg['CantidadHorasExtras'] = 0;
        $leg['SeguroVidaObligatorio'] = 0;
        $leg['ImporteImponibleBecario'] = 0;
        $leg['AporteAdicionalObraSocial'] = 0;
        $leg['ImporteSICOSS27430'] = 0;
        $leg['ImporteSICOSSDec56119'] = 0;
        $leg['ImporteSACDoce'] = 0;
        $leg['ImporteSACAuto'] = 0;
        $leg['ImporteSACNodo'] = 0;
        $leg['ContribTareaDif'] = 0;
        $leg['NoRemun4y8'] = 0;
        $leg['IncrementoSolidario'] = 0;
        $leg['ImporteNoRemun96'] = 0;
        $leg['ImporteTipo91'] = 0;


        $informar_becarios = MapucheConfig::getSicossInformarBecarios();
        $cargoInvestigador = []; // Voy a guardar en esta variable los numeros de cargos que son investigador

        // En el caso de que en check 'Toma en cuenta Familiares a Cargo para informar SICOSS?' en configuración -> impositivos -> parametros sicoss sea false
        // voy a fijarme si se liquido un concepto igual al configurado como obra social familiar a cargo. Informo 0 o 1 (no se liquido o se liquido algun concepto igual al definido)
        if ($this->sicossConfigurationRepository->getCantidadAdherentesSicoss() == 0) {
            $leg['adherentes'] = 0;
        }

        $conceptos_liq_por_leg = $this->consultarConceptosLiquidados($nro_leg, 'true');

        // Sumarizo donde corresponda para cada concepto liquidado
        // Cuando recorro guardo el numero de cargo si es investigador, para luego procesar en calcularSACInvestigador
        $conce_hs_extr = [];
        $cont = 0;
        for ($i = 0; $i < \count($conceptos_liq_por_leg); $i++) {
            $importe = $conceptos_liq_por_leg[$i]['impp_conce'];
            $importe_novedad = $conceptos_liq_por_leg[$i]['nov1_conce'];
            $grupos_concepto = $conceptos_liq_por_leg[$i]['tipos_grupos'];
            $codn_concepto = $conceptos_liq_por_leg[$i]['codn_conce'];
            $nro_cargo = $conceptos_liq_por_leg[$i]['nro_cargo'];
            $codigo_obra_social = $leg['codigo_os'];


            if (preg_match('/[^\d]+6[^\d]+/', $grupos_concepto)) {
                $leg['ImporteHorasExtras'] += $importe;
                // Si tiene el check de sumar horas extras por novedad ademas sumo en horas extras novedad1
                if ($this->sicossConfigurationRepository->getHorasExtrasPorNovedad() == 1) {
                    $horas = $this->sicossCalculoRepository->calculoHorasExtras($codn_concepto, $nro_cargo);
                    //verifico que las hs extras para el concepto determinado no se hayan sumado para sumarlas e informarlas en sicoss
                    if (!\in_array($codn_concepto, $conce_hs_extr)) {
                        $conce_hs_extr[] = $codn_concepto;
                        $leg['CantidadHorasExtras'] += $horas['sum_nov1'];
                    }
                }
            }

            if (preg_match('/[^\d]+7[^\d]+/', $grupos_concepto)) {
                $leg['ImporteZonaDesfavorable'] += $importe;
            }

            if (preg_match('/[^\d]+8[^\d]+/', $grupos_concepto)) {
                $leg['ImporteVacaciones'] += $importe;
            }

            if (preg_match('/[^\d]+9[^\d]+/', $grupos_concepto)) {
                $leg['ImporteSAC'] += $importe;

                if ($conceptos_liq_por_leg[$i]['codigoescalafon'] == 'NODO') {
                    $leg['ImporteSACNodo'] += $importe;
                }
                if ($conceptos_liq_por_leg[$i]['codigoescalafon'] == 'AUTO') {
                    $leg['ImporteSACAuto'] += $importe;
                }
                if ($conceptos_liq_por_leg[$i]['codigoescalafon'] == 'DOCE') {
                    $leg['ImporteSACDoce'] += $importe;
                }
            }

            if (preg_match('/[^\d]+11[^\d]+/', $grupos_concepto)) {
                $leg['ImporteImponible_6'] += $importe;
                if ($leg['PrioridadTipoDeActividad'] < 38) {
                    $leg['PrioridadTipoDeActividad'] = 38;
                }
                if (($leg['PrioridadTipoDeActividad'] == 87) || ($leg['PrioridadTipoDeActividad'] == 88)) {
                    $leg['PrioridadTipoDeActividad'] = 38;
                }
                array_push($cargoInvestigador, $nro_cargo);
            }

            if (preg_match('/[^\d]+12[^\d]+/', $grupos_concepto)) {
                $leg['ImporteImponible_6'] += $importe;
                if ($leg['PrioridadTipoDeActividad'] < 34) {
                    $leg['PrioridadTipoDeActividad'] = 34;
                }
                array_push($cargoInvestigador, $nro_cargo);
            }

            if (preg_match('/[^\d]+13[^\d]+/', $grupos_concepto)) {
                $leg['ImporteImponible_6'] += $importe;
                if ($leg['PrioridadTipoDeActividad'] < 35) {
                    $leg['PrioridadTipoDeActividad'] = 35;
                }
                array_push($cargoInvestigador, $nro_cargo);
            }

            if (preg_match('/[^\d]+14[^\d]+/', $grupos_concepto)) {
                $leg['ImporteImponible_6'] += $importe;
                if ($leg['PrioridadTipoDeActividad'] < 36) {
                    $leg['PrioridadTipoDeActividad'] = 36;
                }
                if ($leg['PrioridadTipoDeActividad'] == 87 || $leg['PrioridadTipoDeActividad'] == 88) {
                    $leg['PrioridadTipoDeActividad'] = 36;
                }
                array_push($cargoInvestigador, $nro_cargo);
            }

            if (preg_match('/[^\d]+15[^\d]+/', $grupos_concepto)) {
                $leg['ImporteImponible_6'] += $importe;
                if ($leg['PrioridadTipoDeActividad'] < 37) {
                    $leg['PrioridadTipoDeActividad'] = 37;
                }
                if ($leg['PrioridadTipoDeActividad'] == 87 || $leg['PrioridadTipoDeActividad'] == 88) {
                    $leg['PrioridadTipoDeActividad'] = 37;
                }
                array_push($cargoInvestigador, $nro_cargo);
            }

            if (preg_match('/[^\d]+16[^\d]+/', $grupos_concepto)) {
                $leg['AporteAdicionalObraSocial'] += $importe;
            }

            if (preg_match('/[^\d]+21[^\d]+/', $grupos_concepto)) {
                $leg['ImporteAdicionales'] += $importe;
            }

            if (preg_match('/[^\d]+22[^\d]+/', $grupos_concepto)) {
                $leg['ImportePremios'] += $importe;
            }

            // conceptos no remunerativos
            if (preg_match('/[^\d]+45[^\d]+/', $grupos_concepto)) {
                $leg['ImporteNoRemun'] += $importe;
            }

            if (preg_match('/[^\d]+46[^\d]+/', $grupos_concepto)) {
                $leg['ImporteRectificacionRemun'] += $importe;
            }

            if (preg_match('/[^\d]+47[^\d]+/', $grupos_concepto)) {
                $leg['ImporteMaternidad'] += $importe;
            }

            if (preg_match('/[^\d]+48[^\d]+/', $grupos_concepto)) {
                $leg['ImporteImponible_6'] += $importe;
                if ($leg['PrioridadTipoDeActividad'] < 36 || $leg['PrioridadTipoDeActividad'] == 88) {
                    $leg['PrioridadTipoDeActividad'] = 87;
                }
                array_push($cargoInvestigador, $nro_cargo);
            }

            if (preg_match('/[^\d]+49[^\d]+/', $grupos_concepto)) {
                $leg['ImporteImponible_6'] += $importe;
                if ($leg['PrioridadTipoDeActividad'] < 36) {
                    $leg['PrioridadTipoDeActividad'] = 88;
                }
                array_push($cargoInvestigador, $nro_cargo);
            }
            if (preg_match('/[^\d]+58[^\d]+/', $grupos_concepto)) {
                $leg['SeguroVidaObligatorio'] = 1;
            }

            if ($this->sicossConfigurationRepository->getCodigoObraSocialAporteAdicional() == $codn_concepto) {
                $leg['IMPORTE_ADICI'] += $importe;
            }

            if ($this->sicossConfigurationRepository->getAportesVoluntarios() == $codn_concepto) {
                $leg['IMPORTE_VOLUN'] += $importe;
            }

            if ($this->sicossConfigurationRepository->getCantidadAdherentesSicoss() == 0 && $this->sicossConfigurationRepository->getCodigoObraSocialFamiliarCargo() == $codn_concepto) {
                $leg['adherentes'] = 1;
            }

            if (preg_match('/[^\d]+24[^\d]+/', $grupos_concepto) && $this->sicossConfigurationRepository->getHorasExtrasPorNovedad() == 0) {
                $leg['CantidadHorasExtras'] += $importe;
            }

            if (preg_match('/[^\d]+67[^\d]+/', $grupos_concepto) && $informar_becarios == 1) {
                $leg['ImporteImponibleBecario'] += $importe;
            }

            if (preg_match('/[^\d]+81[^\d]+/', $grupos_concepto)) {
                $leg['ImporteSICOSS27430'] += $importe;
            }
            if (preg_match('/[^\d]+83[^\d]+/', $grupos_concepto)) {
                $leg['ImporteSICOSSDec56119'] += $importe;
            }

            if (preg_match('/[^\d]+84[^\d]+/', $grupos_concepto)) {
                $leg['NoRemun4y8'] += $importe;
            }

            /*if(preg_match('/[^\d]+85[^\d]+/', $grupos_concepto))
                {
                    $leg['ContribTareaDif'] += $importe;

                }*/

            // #6204 Nuevos campos SICOSS "Incremento Salarial" y "Remuneración 11"
            if (preg_match('/[^\d]+86[^\d]+/', $grupos_concepto)) {
                $leg['IncrementoSolidario'] += $importe;
            }

            // Tipo 91- AFIP Base de Cálculo Diferencial Aportes OS y FSR
            if (preg_match('/[^\d]+91[^\d]+/', $grupos_concepto)) {
                $leg['ImporteTipo91'] += $importe;
            }

            // nuevo tipo de grupo 96, conceptos NoRemun que solo impacten en la Remuneración bruta total
            if (preg_match('/[^\d]+96[^\d]+/', $grupos_concepto)) {
                $leg['ImporteNoRemun96'] += $importe;
            }
        }
        // Segun prioridad selecciono el valor de dha8 o no; se informa TipoDeActividad como codigo de actividad
        if ($leg['PrioridadTipoDeActividad'] == 38 || $leg['PrioridadTipoDeActividad'] == 0) {
            $leg['TipoDeActividad'] = $leg['codigoactividad'];
        } elseif (($leg['PrioridadTipoDeActividad'] >= 34 && $leg['PrioridadTipoDeActividad'] <= 37) ||
            $leg['PrioridadTipoDeActividad'] == 87 || $leg['PrioridadTipoDeActividad'] == 88
        ) {
            $leg['TipoDeActividad'] = $leg['PrioridadTipoDeActividad'];
        }

        $leg['SACInvestigador'] = $this->calcularSACInvestigador($nro_leg, $cargoInvestigador);
    }

    /**
     * Consulta conceptos liquidados para un legajo
     * Método auxiliar extraído de SicossLegacy.
     */
    public function consultarConceptosLiquidados(int $nro_leg, string $where): array
    {
        $sql_conceptos_fltrados =
            "
                                            SELECT
                                                    impp_conce,
                                                    nov1_conce,
                                                       codn_conce,
                                                       tipos_grupos,
                                                       nro_cargo,
                                                       codigoescalafon
                                            FROM
                                                    conceptos_liquidados
                                            WHERE
                                                   nro_legaj = $nro_leg
                                                   AND tipos_grupos IS NOT NULL
                                                   AND $where
                                        ";

        $conceptos_filtrados = DB::connection($this->getConnectionName())->select($sql_conceptos_fltrados);

        // Convertir objetos stdClass a arrays
        return array_map(function ($concepto) {
            return (array)$concepto;
        }, $conceptos_filtrados);
    }

    /**
     * Calcula SAC investigador
     * Método auxiliar extraído de SicossLegacy.
     */
    public function calcularSACInvestigador(int $nro_leg, array $cargos): float
    {
        $sacInvestigador = 0;
        $cargos = array_unique($cargos); // limpio cargos duplicados
        foreach ($cargos as $cargo) {
            $where = " nro_cargo = $cargo
                           --filtro solo los que tienen tipo de concepto = 9 como es una lista uso exp. reg.
                           AND array_to_string(tipos_grupos,',') ~ '(:?^|,)+9(:?$|,)'";
            $conceptos_liq_por_leg = $this->consultarConceptosLiquidados($nro_leg, $where);
            for ($j = 0; $j < \count($conceptos_liq_por_leg); $j++) {
                $sacInvestigador += $conceptos_liq_por_leg[$j]['impp_conce'];
            }
        }

        return $sacInvestigador;
    }

    /**
     * Genera una línea de texto SICOSS formateada según las especificaciones AFIP.
     *
     * @param array $legajo Datos del legajo procesado
     *
     * @return string Línea formateada de 500 caracteres
     */
    protected function generarLineaSicoss(array $legajo): string
    {
        $linea = '';

        // 1. Datos de identificación personal (11 + 30 = 41 caracteres)
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['cuil'] ?? '0', 11);
        $linea .= $this->sicossFormateadorRepository->llenaBlancosModificado($legajo['apyno'] ?? '', 30);

        // 2. Datos familiares (1 + 2 = 3 caracteres)
        $linea .= $legajo['conyugue'] ? '1' : '0';
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['hijos'] ?? '0', 2);

        // 3. Datos situación laboral (2 + 2 + 3 + 2 = 9 caracteres)
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['codigosituacion'] ?? '0', 2);
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['CodigoCondicion'] ?? '0', 2);
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['TipoDeActividad'] ?? '0', 3);
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['codigozona'] ?? '0', 2);

        // 4. Datos aportes y obra social (5 + 3 + 6 + 2 = 16 caracteres)
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['PorcAporteDiferencialJubilacion'] ?? 0, 2, '', ''),
            5,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['codigocontratacion'] ?? '0', 3);
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['codigo_os'] ?? '0', 6);
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['adherentes'] ?? '0', 2);

        // 5. Remuneraciones principales (12 + 12 + 9 + 9 + 9 + 9 + 9 + 50 = 119 caracteres)
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['IMPORTE_BRUTO'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['IMPORTE_IMPON'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['AsignacionesFliaresPagadas'] ?? 0, 2, '', ''),
            9,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['IMPORTE_VOLUN'] ?? 0, 2, '', ''),
            9,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['IMPORTE_ADICI'] ?? 0, 2, '', ''),
            9,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteSACOtroAporte'] ?? 0, 2, '', ''),
            9,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteSACOtraActividad'] ?? 0, 2, '', ''),
            9,
        );
        $linea .= $this->sicossFormateadorRepository->llenaBlancos($legajo['provincia'] ?? 'CABA', 50);

        // 6. Remuneraciones adicionales (12 + 12 + 12 = 36 caracteres)
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteImponiblePatronal'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteBrutoOtraActividad'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteImponible_4'] ?? 0, 2, '', ''),
            12,
        );

        // 7. Datos siniestros y tipo empresa (2 + 1 + 9 + 1 + 9 + 1 = 23 caracteres)
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['codsiniestrado'] ?? '0', 2);
        $linea .= $legajo['marcareduccion'] ?? '0';
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['recompensamrl'] ?? 0, 2, '', ''),
            9,
        );
        $linea .= $legajo['tipoempresa'] ?? '0';
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['AporteAdicionalObraSocial'] ?? 0, 2, '', ''),
            9,
        );
        $linea .= $legajo['regimen'] ?? '0';

        // 8. Situaciones de revista (2 + 2 + 2 + 2 + 2 + 2 = 12 caracteres)
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['codigorevista1'] ?? '0', 2);
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['fecharevista1'] ?? '0', 2);
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['codigorevista2'] ?? '0', 2);
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['fecharevista2'] ?? '0', 2);
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['codigorevista3'] ?? '0', 2);
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['fecharevista3'] ?? '0', 2);

        // 9. Conceptos salariales (12 + 12 + 12 + 12 + 12 = 60 caracteres)
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteSueldoMasAdicionales'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteSAC'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteHorasExtras'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteZonaDesfavorable'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteVacaciones'] ?? 0, 2, '', ''),
            12,
        );

        // 10. Datos laborales (9 + 12 + 1 + 12 + 1 = 35 caracteres)
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['dias_trabajados'] ?? '30', 9);
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteImponible_5'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $legajo['trabajadorconvencionado'] ?? '0';
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteImponible_6'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $legajo['TipoDeOperacion'] ?? '0';

        // 11. Conceptos adicionales (12 + 12 + 12 + 12 + 3 + 12 = 63 caracteres)
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteAdicionales'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImportePremios'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['Remuner78805'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteSICOSS27430'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['CantidadHorasExtras'] ?? '0', 3);
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteNoRemun'] ?? 0, 2, '', ''),
            12,
        );

        // 12. Conceptos especiales (12 + 9 + 12 + 9 = 42 caracteres)
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteMaternidad'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteRectificacionRemun'] ?? 0, 2, '', ''),
            9,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['importeimponible_9'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ContribTareaDif'] ?? 0, 2, '', ''),
            9,
        );

        // 13. Datos finales (3 + 1 + 12 + 12 + 12 = 40 caracteres)
        $linea .= $this->sicossFormateadorRepository->llenaImportes($legajo['horastrabajadas'] ?? '0', 3);
        $linea .= $legajo['SeguroVidaObligatorio'] ?? '0';
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteSICOSSDec56119'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['IncrementoSolidario'] ?? 0, 2, '', ''),
            12,
        );
        $linea .= $this->sicossFormateadorRepository->llenaImportes(
            number_format($legajo['ImporteImponible_6'] ?? 0, 2, '', ''),
            12,
        );

        // Asegurar longitud exacta de 500 caracteres
        return $this->ajustarLongitud($linea, 500);
    }

    /**
     * Ajusta la longitud de una línea a exactamente 500 caracteres.
     *
     * @param string $linea Línea a ajustar
     * @param int $longitud Longitud objetivo (500)
     *
     * @return string Línea ajustada
     */
    protected function ajustarLongitud(string $linea, int $longitud): string
    {
        if (\strlen($linea) > $longitud) {
            return substr($linea, 0, $longitud);
        }
        return str_pad($linea, $longitud, ' ', \STR_PAD_RIGHT);
    }
}
