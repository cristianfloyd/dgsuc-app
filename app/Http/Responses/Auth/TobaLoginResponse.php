<?php

namespace App\Http\Responses\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Livewire\Features\SupportRedirects\Redirector;

class TobaLoginResponse implements LoginResponse
{
    public function toResponse($request)
    {
        return redirect('/selector-panel');
    }
}