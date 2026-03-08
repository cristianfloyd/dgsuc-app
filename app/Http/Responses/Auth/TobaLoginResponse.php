<?php

namespace App\Http\Responses\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;

class TobaLoginResponse implements LoginResponse
{
    public function toResponse($request)
    {
        return redirect('/selector-panel');
    }
}
