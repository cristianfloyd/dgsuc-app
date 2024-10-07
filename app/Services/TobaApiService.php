<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TobaApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.toba.url');
    }

    public function login($username, $password)
    {
        $response = Http::post($this->baseUrl . '/api/login', [
            'username' => $username,
            'password' => $password,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function getUserInfo($token)
    {
        $response = Http::withToken($token)->get($this->baseUrl . '/api/user');

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
