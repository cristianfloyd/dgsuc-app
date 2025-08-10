<?php

namespace App\Strategies;

use App\Contracts\CuilOperationStrategy;
use App\Contracts\CuilRepositoryInterface;
use App\Contracts\WorkflowServiceInterface;

class LoadCuilsNotInAfipStrategy implements CuilOperationStrategy
{
    private $repository;

    private $workflowService;

    private $processLog;

    public function __construct(CuilRepositoryInterface $repository, WorkflowServiceInterface $workflowService, $processLog)
    {
        $this->repository = $repository;
        $this->workflowService = $workflowService;
        $this->processLog = $processLog;
    }

    /**
     * Ejecuta la estrategia de carga de CUILs que no están en AFIP.
     *
     * Esta función se encarga de obtener los CUILs que no se encuentran en AFIP, marcar el paso actual como completado en el flujo de trabajo y devolver un array con información relevante para la interfaz de usuario.
     *
     * @return array|null Un array con información sobre los CUILs que no están en AFIP, o null si no se está en el paso correspondiente.
     */
    public function execute()
    {
        $currentStep = $this->workflowService->getCurrentStep($this->processLog);

        if ($currentStep === 'obtener_cuils_not_in_afip') {
            /**
             * Obtiene los CUILs que no se encuentran en AFIP.
             *
             * Esta función se encarga de recuperar una lista de CUILs que no están registrados en AFIP.
             *
             * @return array Un array con los CUILs que no se encuentran en AFIP.
             */
            $cuilsNotInAfip = $this->repository->getCuilsNotInAfip();

            // Marcar el paso como completado
            $this->workflowService->completeStep($this->processLog, $currentStep);

            return [
                'cuilsNotInAfip' => $cuilsNotInAfip,
                'showCuilsTable' => true,
                'cuilsNotInAfipLoaded' => true,
                'crearTablaTemp' => true,
                'showCreateTempTableButton' => true,
            ];
        }

        return null;
    }
}
