<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class EnvironmentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->ensureEnvironmentFileExists();
        $this->validateEnvironmentVariables();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // No necesitamos hacer nada en el boot
    }

    /**
     * Ensure the environment file exists and is readable.
     */
    private function ensureEnvironmentFileExists(): void
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            Log::error('Environment file not found', ['path' => $envPath]);
            throw new \RuntimeException("Environment file not found at: {$envPath}");
        }

        if (!File::isReadable($envPath)) {
            Log::error('Environment file is not readable', ['path' => $envPath]);
            throw new \RuntimeException("Environment file is not readable at: {$envPath}");
        }

        Log::info('Environment file loaded successfully', ['path' => $envPath]);
    }

    /**
     * Validate that all required environment variables are present.
     */
    private function validateEnvironmentVariables(): void
    {
        $requiredVariables = [
            'APP_NAME',
            'APP_ENV',
            'APP_KEY',
            'APP_DEBUG',
            'APP_URL',
        ];

        $missingVariables = [];

        foreach ($requiredVariables as $variable) {
            if (empty(env($variable))) {
                $missingVariables[] = $variable;
            }
        }

        if (!empty($missingVariables)) {
            Log::warning('Missing environment variables', ['variables' => $missingVariables]);
            
            // Only throw exception for critical variables
            $criticalVariables = ['APP_KEY'];
            $missingCritical = array_intersect($missingVariables, $criticalVariables);
            
            if (!empty($missingCritical)) {
                throw new \RuntimeException(
                    'Critical environment variables are missing: ' . implode(', ', $missingCritical)
                );
            }
        }
    }
}
