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

    public function execute()
    {
        $currentStep = $this->workflowService->getCurrentStep($this->processLog);

        if ($currentStep === 'obtener_cuils_not_in_afip') {
            $cuilsNotInAfip = $this->repository->getCuilsNotInAfip();

            // Marcar el paso como completado
            $this->workflowService->completeStep($this->processLog, $currentStep);

            return [
                'cuilsNotInAfip' => $cuilsNotInAfip,
                'showCuilsTable' => true,
                'cuilsNotInAfipLoaded' => true,
                'crearTablaTemp' => true,
                'showCreateTempTableButton' => true
            ];
        }

        return null;
    }
}
