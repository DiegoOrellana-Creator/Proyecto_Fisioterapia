<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Establecimiento;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServicioController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('admin.servicios.index', [
            'servicios' => Servicio::with('establecimiento')
                ->withCount('profesionales')
                ->when(! $user->esAdminGeneral(), fn ($q) => $q->where('establecimiento_id', $user->establecimiento_id))
                ->when($request->integer('establecimiento'), fn ($q, $id) => $q->where('establecimiento_id', $id))
                ->orderBy('establecimiento_id')->orderBy('categoria')->orderBy('nombre')
                ->get(),
            'establecimientos' => $this->establecimientos($request),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.servicios.form', [
            's' => new Servicio(['activo' => true, 'duracion_min' => 45, 'sesiones_sugeridas' => 1, 'establecimiento_id' => $request->user()->establecimiento_id]),
            'establecimientos' => $this->establecimientos($request),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Servicio::create($this->validar($request));

        return redirect()->route('admin.servicios.index')->with('ok', 'Servicio creado. Asígnalo a los profesionales que lo atienden.');
    }

    public function edit(Request $request, Servicio $servicio): View
    {
        $this->autorizar($request, $servicio);

        return view('admin.servicios.form', ['s' => $servicio, 'establecimientos' => $this->establecimientos($request)]);
    }

    public function update(Request $request, Servicio $servicio): RedirectResponse
    {
        $this->autorizar($request, $servicio);
        $servicio->update($this->validar($request));

        return redirect()->route('admin.servicios.index')->with('ok', 'Servicio actualizado.');
    }

    private function validar(Request $request): array
    {
        $user = $request->user();

        $datos = $request->validate([
            'establecimiento_id' => ['required', 'integer', Rule::exists('establecimientos', 'id')],
            'nombre' => ['required', 'string', 'max:120'],
            'categoria' => ['required', 'string', Rule::in(Servicio::CATEGORIAS)],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'duracion_min' => ['required', 'integer', 'min:15', 'max:240'],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999'],
            'sesiones_sugeridas' => ['required', 'integer', 'min:1', 'max:30'],
        ]);
        $datos['activo'] = $request->boolean('activo');

        if (! $user->esAdminGeneral()) {
            $datos['establecimiento_id'] = $user->establecimiento_id;
        }

        return $datos;
    }

    private function autorizar(Request $request, Servicio $servicio): void
    {
        $user = $request->user();
        abort_unless($user->esAdminGeneral() || $user->establecimiento_id === $servicio->establecimiento_id, 403);
    }

    private function establecimientos(Request $request)
    {
        $user = $request->user();

        return Establecimiento::orderBy('nombre')
            ->when(! $user->esAdminGeneral(), fn ($q) => $q->whereKey($user->establecimiento_id))
            ->get();
    }
}
