<?php

namespace App\Contracts;

use App\Models\ProcessLog;

interface WorkflowServiceInterface
{
    public function startWorkflow(): ProcessLog;
    public function resetWorkflow(ProcessLog $processLog): void;
    public function getLatestWorkflow(): ?ProcessLog;
    public function getSteps(): array;
    public function getCurrentStep(ProcessLog $processLog): string|null;
    public function updateStep(ProcessLog $processLog, string $step, string $status);
    public function completeStep(ProcessLog $processLog, string $step): void;
    public function getNextStep(string $currentStep): ?string;
    public function isStepCompleted(ProcessLog $processLog, string $step): bool;
    public function getStepUrl(string $step): string;
    public function getSubSteps($step): array;
    public function isProcessCompleted(ProcessLog $processLog): bool;
}
