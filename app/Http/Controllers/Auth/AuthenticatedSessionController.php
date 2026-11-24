<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        self::recordarRegreso($request);

        return view('auth.login');
    }

    /**
     * Si el visitante llega desde el formulario de reserva, al ingresar vuelve a él.
     * Solo se aceptan rutas internas para evitar redirecciones a otros sitios.
     */
    public static function recordarRegreso(Request $request): void
    {
        $volver = (string) $request->query('volver', '');
        if (str_starts_with($volver, '/') && ! str_starts_with($volver, '//') && ! str_contains($volver, '\\')) {
            $request->session()->put('url.intended', url($volver));
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
