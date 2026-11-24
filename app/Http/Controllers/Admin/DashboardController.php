<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Establecimiento;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\Valoracion;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $estId = $user->esAdminGeneral() ? null : $user->establecimiento_id;
        $filtro = fn ($q) => $estId ? $q->where('establecimiento_id', $estId) : $q;

        $desde = now()->subDays(30)->toDateString();
        $hoy = now()->toDateString();

        $porEstado = $filtro(Cita::query())
            ->whereBetween('fecha', [$desde, $hoy])
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $atendidas = (int) ($porEstado[Cita::ATENDIDA] ?? 0);
        $faltas = (int) ($porEstado[Cita::NO_ASISTIO] ?? 0);

        $porProfesional = $filtro(Profesional::query())
            ->with('user')
            ->withCount([
                'citas as atendidas' => fn ($q) => $q->where('estado', Cita::ATENDIDA)->where('fecha', '>=', $desde),
                'citas as faltas' => fn ($q) => $q->where('estado', Cita::NO_ASISTIO)->where('fecha', '>=', $desde),
                'citas as proximas' => fn ($q) => $q->activas()->where('fecha', '>=', $hoy),
            ])
            ->withAvg('valoraciones', 'puntuacion')
            ->get();

        return view('admin.dashboard', [
            'establecimiento' => $estId ? Establecimiento::find($estId) : null,
            'porEstado' => $porEstado,
            'tasaInasistencia' => ($atendidas + $faltas) ? round($faltas * 100 / ($atendidas + $faltas), 1) : null,
            'porConfirmar' => $filtro(Cita::query())->whereIn('estado', [Cita::SOLICITADA, Cita::REPROGRAMADA])->where('fecha', '>=', $hoy)->count(),
            'hoy' => $filtro(Cita::query())->activas()->where('fecha', $hoy)->count(),
            'ingresos' => $filtro(Cita::query())->where('estado', Cita::ATENDIDA)->whereBetween('fecha', [$desde, $hoy])->sum('precio'),
            'promedio' => $filtro(Valoracion::query())->avg('puntuacion'),
            'porProfesional' => $porProfesional,
            'conteos' => [
                'establecimientos' => $estId ? 1 : Establecimiento::count(),
                'profesionales' => $filtro(Profesional::query())->count(),
                'servicios' => $filtro(Servicio::query())->count(),
            ],
            'sugerencias' => $estId ? collect() : \App\Models\Sugerencia::where('revisada', false)->latest()->take(8)->get(),
            'ultimasValoraciones' => $filtro(Valoracion::query())->with(['paciente', 'profesional.user'])->latest()->take(4)->get(),
        ]);
    }
}
