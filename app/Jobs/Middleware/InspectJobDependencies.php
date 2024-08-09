<?php

namespace App\Jobs\Middleware;

use ReflectionClass;
use Illuminate\Support\Facades\Log;

class InspectJobDependencies
{
    public function handle($job, $next)
    {
        $reflection = new ReflectionClass($job);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($job);
            Log::info("Job dependency: {$property->getName()} - " . get_class($value));
        }

        return $next($job);
    }
}
