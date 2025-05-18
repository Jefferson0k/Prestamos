<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse{
        $request->authenticate();
        $request->session()->regenerate();
        $user = Auth::user();
        if (!$user->status) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'status' => 'Tu cuenta está inactiva. Contacta al administrador.',
            ]);
        }
        cache()->put('user-is-online-' . $user->id, true);
        if ($user->restablecimiento == 0) {
            return redirect()->route('usuario.restablecer');
        }
        return redirect()->intended(route('dashboard', absolute: false));
    }
    /**
     * Destroy an authenticated session.
     */
    // En tu controlador AuthenticatedSessionController
    public function destroy(Request $request): RedirectResponse{
        cache()->forget('user-is-online-' . Auth::id());
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

}
