<?php

namespace App;

use Ramsey\Uuid\Uuid;

class CreateUuid
{
    protected $uuid;

    public function __construct()
    {

    }

    public function getUuid()
    {
        $this->uuid = $this->generateUuid();
        return $this->uuid;
    }

    private function generateUuid()
    {
        return Uuid::uuid4()->toString();
    }
}
