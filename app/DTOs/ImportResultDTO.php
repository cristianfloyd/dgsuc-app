<?php

namespace App\DTOs;

class ImportResultDTO
{
    private int $processedCount = 0;
    private int $duplicateCount = 0;
    private int $errorCount = 0;
    private array $errors = [];

    public function __construct(
        public bool $success = false,
        public string $message = '',
        public array $processedData = [],
        public ?\Throwable $error = null
    ) {}

    public function incrementProcessedCount(): void
    {
        $this->processedCount++;
    }

    public function incrementDuplicateCount(): void
    {
        $this->duplicateCount++;
    }

    public function incrementErrorCount(): void
    {
        $this->errorCount++;
    }

    public function addError(string $message, array $context = []): void
    {
        $this->errors[] = [
            'message' => $message,
            'context' => $context,
            'timestamp' => now()
        ];
        $this->incrementErrorCount();
    }

    public function getProcessedCount(): int
    {
        return $this->processedCount;
    }

    public function getDuplicateCount(): int
    {
        return $this->duplicateCount;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'processedCount' => $this->processedCount,
            'duplicateCount' => $this->duplicateCount,
            'errorCount' => $this->errorCount,
            'errors' => $this->errors,
            'processedData' => $this->processedData
        ];
    }
}

