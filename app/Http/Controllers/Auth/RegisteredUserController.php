<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        AuthenticatedSessionController::recordarRegreso($request);

        return view('auth.register');
    }

    /**
     * El registro público crea cuentas de paciente. Los profesionales y
     * administradores los da de alta un administrador.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'telefono' => ['required', 'string', 'max:20'],
            'ci' => ['nullable', 'string', 'max:20', 'unique:users,ci'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:100', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'telefono' => $request->telefono,
            'ci' => $request->ci ?: null,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => User::ROL_PACIENTE,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended(route('paciente.citas', absolute: false));
    }
}
