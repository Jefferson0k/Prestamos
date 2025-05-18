<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class CheckPasswordReset{
    public function handle(Request $request, Closure $next): Response{
        $user = Auth::user();
        if ($user && $user->restablecimiento == 0 && !$request->is('usuario/restablecer*')) {
            return redirect()->route('usuario.restablecer');
        }        
        return $next($request);
    }
}
