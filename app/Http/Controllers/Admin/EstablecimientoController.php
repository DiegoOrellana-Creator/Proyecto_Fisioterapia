<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establecimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EstablecimientoController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user->esAdminGeneral()) {
            return redirect()->route('admin.establecimientos.edit', $user->establecimiento);
        }

        return view('admin.establecimientos.index', [
            'establecimientos' => Establecimiento::withCount(['profesionales', 'servicios', 'citas'])->orderBy('nombre')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->esAdminGeneral(), 403);

        return view('admin.establecimientos.form', ['e' => new Establecimiento(['activo' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->esAdminGeneral(), 403);

        $datos = $this->validar($request);
        $datos['slug'] = $this->slugUnico($datos['nombre']);

        $e = Establecimiento::create($datos);

        return redirect()->route('admin.establecimientos.edit', $e)->with('ok', 'Establecimiento creado. Ahora agrega sus servicios y profesionales.');
    }

    public function edit(Request $request, Establecimiento $establecimiento): View
    {
        $this->autorizar($request, $establecimiento);

        return view('admin.establecimientos.form', ['e' => $establecimiento]);
    }

    public function update(Request $request, Establecimiento $establecimiento): RedirectResponse
    {
        $this->autorizar($request, $establecimiento);

        $datos = $this->validar($request, $establecimiento);
        if (! $request->user()->esAdminGeneral()) {
            unset($datos['activo']);
        }

        $establecimiento->update($datos);

        return redirect()->route('admin.establecimientos.edit', $establecimiento)->with('ok', 'Cambios guardados.');
    }

    private function validar(Request $request, ?Establecimiento $e = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120', Rule::unique('establecimientos', 'nombre')->ignore($e?->id)],
            'descripcion' => ['nullable', 'string', 'max:1500'],
            'direccion' => ['required', 'string', 'max:200'],
            'zona' => ['required', 'string', 'max:80'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'horario_texto' => ['nullable', 'string', 'max:120'],
        ]);
        $datos['activo'] = $request->boolean('activo');

        return $datos;
    }

    private function autorizar(Request $request, Establecimiento $e): void
    {
        $user = $request->user();
        abort_unless($user->esAdminGeneral() || $user->establecimiento_id === $e->id, 403);
    }

    private function slugUnico(string $nombre): string
    {
        $base = Str::slug($nombre);
        $slug = $base;
        $i = 2;
        while (Establecimiento::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
