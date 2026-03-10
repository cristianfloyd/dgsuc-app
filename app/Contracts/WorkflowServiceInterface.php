<?php

namespace App\Contracts;

use App\Models\ProcessLog;

/**
 * Interfce WorkflowInterface que define los métodos para el servicio de flujo de trabajo.
 * Metodos para iniciar, restablecer, obtener y actualizar el flujo de trabajo,
 * así como para obtener información sobre los pasos y el estado del proceso.
 *
 *
 * @method ProcessLog startWorkflow()
 * @method void resetWorkflow(ProcessLog $processLog)
 * @method ProcessLog|null getLatestWorkflow()
 * @method array getSteps()
 * @method string|null getCurrentStep(ProcessLog $processLog)
 * @method mixed updateStep(ProcessLog $processLog, string $step, string $status)
 * @method void completeStep(ProcessLog $processLog, string $step)
 * @method string|null getNextStep(string $currentStep)
 * @method bool isStepCompleted(ProcessLog $processLog, string $step)
 * @method string|null getStepUrl()
 * @method bool isProcessCompleted(ProcessLog $processLog)
 *
 * @version 1.0.0
 *
 * @author Cristian Arenas <cristianfloyd@gmail.com>
 * @license MIT
 * @copyright 2024 Cristian Flores
 *
 * @link https://github.com/cristianfloyd/informes-app
 *
 * @category Contracts
 *
 * @see ProcessLog
 * @see WorkflowService
 * @see ProcessLogService
 */
interface WorkflowServiceInterface
{
    /**
     * Inicia un nuevo flujo de trabajo.
     */
    public function startWorkflow(): ProcessLog;

    /**
     * Reinicia un flujo de trabajo existente.
     */
    public function resetWorkflow(ProcessLog $processLog): void;

    /**
     * Obtiene el flujo de trabajo más reciente.
     */
    public function getLatestWorkflow(): ?ProcessLog;

    /**
     * Obtiene todos los pasos del flujo de trabajo.
     */
    public function getSteps(): array;

    /**
     * Obtiene el paso actual del flujo de trabajo.
     */
    public function getCurrentStep(ProcessLog $processLog): ?string;

    /**
     * Actualiza el estado de un paso en el flujo de trabajo.
     *
     *
     * @return mixed
     */
    public function updateStep(ProcessLog $processLog, string $step, string $status);

    /**
     * Marca un paso como completado en el flujo de trabajo.
     */
    public function completeStep(ProcessLog $processLog, string $step): void;

    /**
     * Obtiene el siguiente paso en el flujo de trabajo.
     */
    public function getNextStep(string $currentStep): ?string;

    /**
     * Verifica si un paso está completado en el flujo de trabajo.
     */
    public function isStepCompleted(ProcessLog $processLog, string $step): bool;

    /**
     * Obtiene la URL asociada a un paso del flujo de trabajo.
     */
    public function getStepUrl(?string $step): ?string;

    /**
     * Marca un paso como fallido en el flujo de trabajo.
     *
     * @param string $step El paso que se ha marcado como fallido.
     * @param string|null $message Un mensaje opcional que describe el motivo del fallo.
     */
    public function failStep(string $step, ?string $message = null): void;

    /**
     * Verifica si el proceso completo está finalizado.
     */
    public function isProcessCompleted(ProcessLog $processLog): bool;
}
