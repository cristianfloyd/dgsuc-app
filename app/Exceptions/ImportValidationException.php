<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Collection;

class ImportValidationException extends Exception
{
    protected Collection $failures;
    protected array $rowData;
    protected int $rowNumber;

    /**
     * Constructor de la excepción de validación
     *
     * @param string $message Mensaje de error
     * @param array $rowData Datos de la fila que causó el error
     * @param int $rowNumber Número de fila en el archivo
     * @param int $code Código de error
     * @param \Throwable|null $previous Excepción previa
     */
    public function __construct(
        string $message,
        array $rowData = [],
        int $rowNumber = 0,
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->rowData = $rowData;
        $this->rowNumber = $rowNumber;
        $this->failures = new Collection();
    }

    /**
     * Obtiene los datos de la fila que causó el error
     *
     * @return array
     */
    public function getRowData(): array
    {
        return $this->rowData;
    }

    /**
     * Obtiene el número de fila donde ocurrió el error
     *
     * @return int
     */
    public function getRowNumber(): int
    {
        return $this->rowNumber;
    }

    /**
     * Agrega un error de validación
     *
     * @param string $attribute
     * @param string $error
     * @return void
     */
    public function addFailure(string $attribute, string $error): void
    {
        $this->failures->push([
            'attribute' => $attribute,
            'error' => $error,
            'row' => $this->rowNumber,
            'values' => $this->rowData
        ]);
    }

    /**
     * Obtiene todos los errores de validación
     *
     * @return Collection
     */
    public function getFailures(): Collection
    {
        return $this->failures;
    }

    /**
     * Formatea el error para logging
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'row_number' => $this->rowNumber,
            'row_data' => $this->rowData,
            'failures' => $this->failures->toArray()
        ];
    }
}
