<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\Sugerencia;
use App\Models\Valoracion;
use App\Services\Disponibilidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Páginas públicas: portada, búsqueda de establecimientos, catálogo de servicios y perfiles.
 */
class SitioController extends Controller
{
    public function inicio(Disponibilidad $disponibilidad): View
    {
        return view('sitio.inicio', [
            'proximos' => $disponibilidad->proximosLibres(5),
            'categorias' => Servicio::activos()->select('categoria')->distinct()->orderBy('categoria')->pluck('categoria'),
            'zonas' => Establecimiento::activos()->select('zona')->distinct()->orderBy('zona')->pluck('zona'),
            'totales' => [
                'establecimientos' => Establecimiento::activos()->count(),
                'profesionales' => Profesional::activos()->count(),
                'servicios' => Servicio::activos()->count(),
            ],
            'destacados' => Establecimiento::activos()
                ->withCount(['profesionales', 'servicios'])
                ->withAvg('valoraciones', 'puntuacion')
                ->orderByDesc('valoraciones_avg_puntuacion')
                ->take(3)->get(),
        ]);
    }

    public function establecimientos(Request $request, Disponibilidad $disponibilidad): View
    {
        $q = trim((string) $request->query('q'));
        $zona = $request->query('zona');
        $categoria = $request->query('categoria');
        $orden = in_array($request->query('orden'), ['valoracion', 'precio', 'nombre'], true) ? $request->query('orden') : 'valoracion';

        $establecimientos = Establecimiento::activos()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%'.mb_strtolower($q).'%';
                $query->where(function ($w) use ($like) {
                    $w->whereRaw('LOWER(nombre) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(descripcion) LIKE ?', [$like])
                        ->orWhereHas('servicios', fn ($s) => $s->whereRaw('LOWER(nombre) LIKE ?', [$like]))
                        ->orWhereHas('profesionales.user', fn ($u) => $u->whereRaw("LOWER(nombre || ' ' || apellido) LIKE ?", [$like]));
                });
            })
            ->when($zona, fn ($query) => $query->where('zona', $zona))
            ->when($categoria, fn ($query) => $query->whereHas('servicios', fn ($s) => $s->activos()->where('categoria', $categoria)))
            ->withCount(['profesionales' => fn ($p) => $p->where('activo', true)])
            ->withAvg('valoraciones', 'puntuacion')
            ->withCount('valoraciones')
            ->withMin(['servicios' => fn ($s) => $s->where('activo', true)], 'precio')
            ->with(['servicios' => fn ($s) => $s->activos()->orderBy('precio')])
            // Subconsultas con COALESCE: ordenan igual en PostgreSQL y SQLite aunque falten datos
            ->when($orden === 'valoracion', fn ($query) => $query->orderByDesc(
                Valoracion::selectRaw('COALESCE(AVG(puntuacion), 0)')->whereColumn('establecimiento_id', 'establecimientos.id')
            ))
            ->when($orden === 'precio', fn ($query) => $query->orderBy(
                Servicio::selectRaw('COALESCE(MIN(precio), 999999)')->whereColumn('establecimiento_id', 'establecimientos.id')->where('activo', true)
            ))
            ->orderBy('nombre')
            ->paginate(8)
            ->withQueryString();

        // Estado de disponibilidad de cada resultado: libre hoy, próximo día libre o sin horarios esta semana
        $proximos = $establecimientos->getCollection()->mapWithKeys(
            fn ($e) => [$e->id => $disponibilidad->proximoLibreEstablecimiento($e)]
        );

        return view('sitio.establecimientos', [
            'establecimientos' => $establecimientos,
            'proximos' => $proximos,
            'totalCatalogo' => Establecimiento::activos()->count(),
            'zonas' => Establecimiento::activos()->select('zona')->distinct()->orderBy('zona')->pluck('zona'),
            'categorias' => Servicio::activos()->select('categoria')->distinct()->orderBy('categoria')->pluck('categoria'),
            'filtros' => compact('q', 'zona', 'categoria'),
            'orden' => $orden,
        ]);
    }

    /** Un paciente pide incorporar un establecimiento que no encontró. */
    public function sugerir(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'establecimiento' => ['required', 'string', 'max:120'],
            'zona' => ['nullable', 'string', 'max:80'],
            'contacto' => ['nullable', 'string', 'max:120'],
            'comentario' => ['nullable', 'string', 'max:500'],
        ], [
            'establecimiento.required' => 'Escribe el nombre del establecimiento que buscas.',
        ]);

        Sugerencia::create($datos + ['user_id' => $request->user()?->id]);

        return redirect()->to(url()->previous().'#sugerir')
            ->with('sugerencia', 'Gracias. Le pediremos a «'.$datos['establecimiento'].'» que publique su agenda.');
    }

    public function establecimiento(Establecimiento $establecimiento): View
    {
        abort_unless($establecimiento->activo, 404);

        $establecimiento->load([
            'servicios' => fn ($s) => $s->activos()->orderBy('categoria')->orderBy('precio'),
            'profesionales' => fn ($p) => $p->activos()->with(['user', 'horarios', 'servicios'])->withAvg('valoraciones', 'puntuacion'),
        ]);

        return view('sitio.establecimiento', [
            'e' => $establecimiento,
            'valoraciones' => $establecimiento->valoraciones()->with(['paciente', 'profesional.user'])->latest()->take(6)->get(),
            'promedio' => $establecimiento->valoraciones()->avg('puntuacion'),
            'totalValoraciones' => $establecimiento->valoraciones()->count(),
        ]);
    }

    public function profesional(Profesional $profesional): View
    {
        abort_unless($profesional->activo && $profesional->establecimiento->activo, 404);

        $profesional->load(['user', 'establecimiento', 'horarios', 'servicios' => fn ($s) => $s->activos()]);

        return view('sitio.profesional', [
            'p' => $profesional,
            'valoraciones' => $profesional->valoraciones()->with('paciente')->latest()->take(8)->get(),
            'promedio' => $profesional->valoraciones()->avg('puntuacion'),
            'totalValoraciones' => $profesional->valoraciones()->count(),
        ]);
    }

    public function servicios(Request $request): View
    {
        $categoria = $request->query('categoria');
        $orden = $request->query('orden', 'precio');

        $servicios = Servicio::activos()
            ->whereHas('establecimiento', fn ($e) => $e->where('activo', true))
            ->whereHas('profesionales', fn ($p) => $p->where('activo', true))
            ->when($categoria, fn ($q) => $q->where('categoria', $categoria))
            ->with('establecimiento')
            ->withCount(['profesionales' => fn ($p) => $p->where('activo', true)])
            ->when($orden === 'duracion', fn ($q) => $q->orderBy('duracion_min'))
            ->when($orden === 'nombre', fn ($q) => $q->orderBy('nombre'))
            ->when($orden === 'precio', fn ($q) => $q->orderBy('precio'))
            ->paginate(12)
            ->withQueryString();

        return view('sitio.servicios', [
            'servicios' => $servicios,
            'categorias' => Servicio::activos()->select('categoria')->distinct()->orderBy('categoria')->pluck('categoria'),
            'categoria' => $categoria,
            'orden' => $orden,
        ]);
    }

    public function comoFunciona(): View
    {
        return view('sitio.como-funciona');
    }
}
