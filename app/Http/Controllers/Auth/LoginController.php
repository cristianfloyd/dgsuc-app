<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    protected function guard()
    {
        return Auth::guard('toba');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('username', 'password');

        if ($this->attemptLogin($request)) {
            $this->sendLoginSuccessResponse($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    protected function sendLoginSuccessResponse(Request $request)
    {
        return response()->json(['message' => 'Inicio de sesión exitoso'], 200);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        return response()->json(['message' => 'Credenciales inválidas'], 401);
    }

    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    protected function credentials(Request $request)
    {
        return $request->only('username', 'password');
    }
}
