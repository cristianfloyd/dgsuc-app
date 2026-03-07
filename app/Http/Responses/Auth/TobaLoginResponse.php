<?php

namespace App\Http\Responses\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;

class TobaLoginResponse implements LoginResponse
{
    public function toResponse($request)
    {
        return redirect('/selector-panel');
    }
}
