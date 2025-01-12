<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Collection;

class ValidationException extends Exception
{
    private array $errors = [];
    private ?string $field = null;

    public function __construct(
        string $message,
        private readonly array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function setField(string $field): self
    {
        $this->field = $field;
        return $this;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function addError(string $field, string $message): self
    {
        $this->errors[$field] = $message;
        return $this;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'field' => $this->field,
            'errors' => $this->errors,
            'context' => $this->context
        ];
    }
}
