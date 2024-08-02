<?php

namespace App\Services;

use App\Contracts\MessageManagerInterface;

class MessageManager implements MessageManagerInterface
{
    protected $messages = [];


    public function addMessage(string $message, string $type = 'info'): void
    {
        $this->messages[] = [
            'message' => $message,
            'type' => $type
        ];
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function clearMessages(): void
    {
        $this->messages = [];
    }
}
