<?php

namespace App\Contracts;

interface MessageManagerInterface
{
    public function addMessage(string $message, string $type = 'info'): void;
    public function getMessages(): array;
    public function clearMessages(): void;
}
