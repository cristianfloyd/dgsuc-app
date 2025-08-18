<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Environment File Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file handles the loading of environment variables
    | in a more robust way, especially for cross-platform compatibility.
    |
    */

    'file_path' => base_path('.env'),
    
    'fallback_paths' => [
        base_path('.env.local'),
        base_path('.env.production'),
        base_path('.env.staging'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Loading Strategy
    |--------------------------------------------------------------------------
    |
    | Defines how the environment file should be loaded:
    | - 'auto': Automatically detect and load the best available file
    | - 'force': Force load a specific file
    | - 'fallback': Try multiple files in order
    |
    */
    'strategy' => env('ENV_LOADING_STRATEGY', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Required Environment Variables
    |--------------------------------------------------------------------------
    |
    | List of environment variables that must be present for the application
    | to function properly.
    |
    */
    'required_variables' => [
        'APP_NAME',
        'APP_ENV',
        'APP_KEY',
        'APP_DEBUG',
        'APP_URL',
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Validation
    |--------------------------------------------------------------------------
    |
    | Whether to validate environment variables on application boot.
    |
    */
    'validate_on_boot' => env('ENV_VALIDATE_ON_BOOT', true),
];
