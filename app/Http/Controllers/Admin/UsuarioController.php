<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Módulo de usuarios (solo administrador general): pacientes, profesionales y administradores.
 */
class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $rol = $request->query('rol');
        $q = trim((string) $request->query('q'));

        $usuarios = User::query()
            ->with('establecimiento')
            ->when($rol, fn ($w) => $w->where('rol', $rol))
            ->when($q !== '', function ($w) use ($q) {
                $like = '%'.mb_strtolower($q).'%';
                $w->where(fn ($x) => $x->whereRaw("LOWER(nombre || ' ' || apellido) LIKE ?", [$like])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$like])
                    ->orWhere('ci', 'like', $like));
            })
            ->withCount([
                'citas as atendidas' => fn ($c) => $c->where('estado', Cita::ATENDIDA),
                'citas as faltas' => fn ($c) => $c->where('estado', Cita::NO_ASISTIO),
            ])
            ->orderBy('rol')->orderBy('apellido')
            ->paginate(20)->withQueryString();

        return view('admin.usuarios.index', compact('usuarios', 'rol', 'q'));
    }

    public function create(): View
    {
        return view('admin.usuarios.create', ['establecimientos' => Establecimiento::orderBy('nombre')->get()]);
    }

    /** Alta de administradores (generales o de un establecimiento). */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'establecimiento_id' => ['nullable', 'integer', Rule::exists('establecimientos', 'id')],
            'password' => ['required', Password::min(8)],
        ]);

        User::create($datos + [
            'rol' => User::ROL_ADMIN,
            'password' => Hash::make($datos['password']),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.usuarios.index', ['rol' => 'admin'])->with('ok', 'Administrador creado.');
    }

    public function toggle(Request $request, User $usuario): RedirectResponse
    {
        abort_if($usuario->id === $request->user()->id, 422, 'No puedes desactivar tu propia cuenta.');

        $usuario->activo = ! $usuario->activo;
        $usuario->save();
        $usuario->profesional?->update(['activo' => $usuario->activo]);

        return back()->with('ok', $usuario->nombre_completo.($usuario->activo ? ' fue activado.' : ' fue desactivado y ya no puede ingresar.'));
    }
}
