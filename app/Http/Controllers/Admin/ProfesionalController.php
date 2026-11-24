<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establecimiento;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfesionalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('admin.profesionales.index', [
            'profesionales' => Profesional::with(['user', 'establecimiento', 'servicios'])
                ->withCount('horarios')
                ->when(! $user->esAdminGeneral(), fn ($q) => $q->where('establecimiento_id', $user->establecimiento_id))
                ->orderBy('establecimiento_id')
                ->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.profesionales.form', [
            'p' => new Profesional(['activo' => true, 'establecimiento_id' => $request->user()->establecimiento_id]),
            'u' => new User(),
            ...$this->opciones($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        $profesional = DB::transaction(function () use ($datos) {
            $user = User::create([
                'nombre' => $datos['nombre'],
                'apellido' => $datos['apellido'],
                'email' => $datos['email'],
                'telefono' => $datos['telefono'] ?? null,
                'ci' => $datos['ci'] ?? null,
                'password' => Hash::make($datos['password']),
                'rol' => User::ROL_PROFESIONAL,
                'email_verified_at' => now(),
            ]);

            $p = Profesional::create([
                'user_id' => $user->id,
                ...collect($datos)->only(['establecimiento_id', 'matricula', 'especialidad', 'biografia', 'anios_experiencia', 'activo'])->all(),
            ]);
            $p->servicios()->sync($datos['servicios'] ?? []);

            return $p;
        });

        return redirect()->route('gestion.horarios', $profesional)->with('ok', 'Profesional registrado. Ahora configura sus horarios de atención.');
    }

    public function edit(Request $request, Profesional $profesional): View
    {
        $this->authorize('gestionar', $profesional);

        return view('admin.profesionales.form', [
            'p' => $profesional->load('servicios'),
            'u' => $profesional->user,
            ...$this->opciones($request),
        ]);
    }

    public function update(Request $request, Profesional $profesional): RedirectResponse
    {
        $this->authorize('gestionar', $profesional);

        $datos = $this->validar($request, $profesional);

        DB::transaction(function () use ($datos, $profesional) {
            $profesional->user->fill(collect($datos)->only(['nombre', 'apellido', 'email', 'telefono', 'ci'])->all());
            if (! empty($datos['password'])) {
                $profesional->user->password = Hash::make($datos['password']);
            }
            $profesional->user->activo = $datos['activo'];
            $profesional->user->save();

            $profesional->update(collect($datos)->only(['establecimiento_id', 'matricula', 'especialidad', 'biografia', 'anios_experiencia', 'activo'])->all());
            $profesional->servicios()->sync($datos['servicios'] ?? []);
        });

        return redirect()->route('admin.profesionales.index')->with('ok', 'Datos de '.$profesional->user->nombre_completo.' actualizados.');
    }

    private function validar(Request $request, ?Profesional $p = null): array
    {
        $user = $request->user();
        $userId = $p?->user_id;

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($userId)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'ci' => ['nullable', 'string', 'max:20', Rule::unique('users', 'ci')->ignore($userId)],
            'password' => [$p ? 'nullable' : 'required', Password::min(8)],
            'establecimiento_id' => ['required', 'integer', Rule::exists('establecimientos', 'id')],
            'matricula' => ['nullable', 'string', 'max:40', Rule::unique('profesionales', 'matricula')->ignore($p?->id)],
            'especialidad' => ['required', 'string', 'max:120'],
            'biografia' => ['nullable', 'string', 'max:1500'],
            'anios_experiencia' => ['required', 'integer', 'min:0', 'max:60'],
            'servicios' => ['nullable', 'array'],
            'servicios.*' => ['integer', Rule::exists('servicios', 'id')],
        ]);
        $datos['activo'] = $request->boolean('activo');

        if (! $user->esAdminGeneral()) {
            $datos['establecimiento_id'] = $user->establecimiento_id;
        }

        // Solo se pueden asignar servicios del mismo establecimiento
        $datos['servicios'] = Servicio::whereIn('id', $datos['servicios'] ?? [])
            ->where('establecimiento_id', $datos['establecimiento_id'])
            ->pluck('id')->all();

        return $datos;
    }

    private function opciones(Request $request): array
    {
        $user = $request->user();

        return [
            'establecimientos' => Establecimiento::orderBy('nombre')
                ->when(! $user->esAdminGeneral(), fn ($q) => $q->whereKey($user->establecimiento_id))
                ->with(['servicios' => fn ($s) => $s->orderBy('nombre')])
                ->get(),
        ];
    }
}
