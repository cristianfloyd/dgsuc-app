<?php

namespace App\Jobs\Middleware;

use Illuminate\Support\Facades\Log;

class InspectJobDependencies
{
    public function handle($job, $next)
    {
        $reflection = new \ReflectionClass($job);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($job);
            Log::info("Job dependency: {$property->getName()} - " . (\is_object($value) ? $value::class : \gettype($value)));
        }

        return $next($job);
    }
}
