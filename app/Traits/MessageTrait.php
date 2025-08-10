<?php

namespace App\Traits;

trait MessageTrait
{
    public $message;

    /** Set the message to be displayed.
     *
     * @param string $message The message to be displayed.
     */
    public function showMessage($message): void
    {
        $this->message = $message;
    }
}
