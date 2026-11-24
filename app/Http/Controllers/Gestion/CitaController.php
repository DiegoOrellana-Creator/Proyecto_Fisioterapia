<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Profesional;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestión de citas para profesionales y administradores:
 * agenda, confirmación, registro de asistencia y seguimiento de pacientes.
 */
class CitaController extends Controller
{
    /** Agenda semanal (profesional) o del establecimiento (administrador). */
    public function agenda(Request $request): View
    {
        $user = $request->user();
        $inicioSemana = Carbon::parse($request->query('semana', now()->toDateString()))->startOfWeek();
        $finSemana = $inicioSemana->copy()->endOfWeek();

        $profesionales = $this->profesionalesVisibles($user)->with('user')->get();
        $profesionalId = $user->esProfesional()
            ? $user->profesional?->id
            : ($request->integer('profesional') ?: null);

        $citas = $this->alcance($user)
            ->with(['paciente', 'servicio', 'profesional.user'])
            ->whereBetween('fecha', [$inicioSemana->toDateString(), $finSemana->toDateString()])
            ->when($profesionalId, fn ($q) => $q->where('profesional_id', $profesionalId))
            ->whereNotIn('estado', [Cita::CANCELADA])
            ->orderBy('fecha')->orderBy('hora_inicio')
            ->get()
            ->groupBy(fn ($c) => $c->fecha->toDateString());

        $porConfirmar = $this->alcance($user)
            ->with(['paciente', 'servicio', 'profesional.user'])
            ->whereIn('estado', [Cita::SOLICITADA, Cita::REPROGRAMADA])
            ->where('fecha', '>=', now()->toDateString())
            ->when($profesionalId, fn ($q) => $q->where('profesional_id', $profesionalId))
            ->orderBy('fecha')->orderBy('hora_inicio')
            ->get();

        $sinRegistrar = $this->alcance($user)
            ->with(['paciente', 'servicio', 'profesional.user'])
            ->activas()
            ->where('fecha', '<=', now()->toDateString())
            ->when($profesionalId, fn ($q) => $q->where('profesional_id', $profesionalId))
            ->orderByDesc('fecha')
            ->get()
            ->filter(fn ($c) => $c->yaPaso());

        return view('gestion.agenda', [
            'inicioSemana' => $inicioSemana,
            'citas' => $citas,
            'porConfirmar' => $porConfirmar,
            'sinRegistrar' => $sinRegistrar,
            'profesionales' => $profesionales,
            'profesionalId' => $profesionalId,
        ]);
    }

    /** Listado con filtros (estado, fecha, profesional, búsqueda de paciente). */
    public function index(Request $request): View
    {
        $user = $request->user();

        $citas = $this->alcance($user)
            ->with(['paciente', 'servicio', 'profesional.user', 'establecimiento'])
            ->when($request->query('estado'), fn ($q, $e) => $q->where('estado', $e))
            ->when($request->query('desde'), fn ($q, $d) => $q->where('fecha', '>=', $d))
            ->when($request->query('hasta'), fn ($q, $h) => $q->where('fecha', '<=', $h))
            ->when($request->integer('profesional'), fn ($q, $p) => $q->where('profesional_id', $p))
            ->when(trim((string) $request->query('q')), function ($q, $texto) {
                $like = '%'.mb_strtolower($texto).'%';
                $q->where(fn ($w) => $w->whereRaw('LOWER(codigo) LIKE ?', [$like])
                    ->orWhereHas('paciente', fn ($p) => $p->whereRaw("LOWER(nombre || ' ' || apellido) LIKE ?", [$like])->orWhere('ci', 'like', $like)));
            })
            ->orderByDesc('fecha')->orderByDesc('hora_inicio')
            ->paginate(15)->withQueryString();

        return view('gestion.citas', [
            'citas' => $citas,
            'profesionales' => $this->profesionalesVisibles($user)->with('user')->get(),
            'filtros' => $request->only(['estado', 'desde', 'hasta', 'profesional', 'q']),
        ]);
    }

    public function show(Cita $cita): View
    {
        $this->authorize('gestionar', $cita);

        $cita->load(['paciente', 'profesional.user', 'servicio', 'establecimiento', 'historial.user', 'valoracion']);

        return view('gestion.cita', [
            'cita' => $cita,
            'resumenPaciente' => $this->resumenPaciente($cita->paciente, request()->user()),
        ]);
    }

    public function confirmar(Request $request, Cita $cita): RedirectResponse
    {
        $this->authorize('gestionar', $cita);
        abort_unless(in_array($cita->estado, [Cita::SOLICITADA, Cita::REPROGRAMADA], true) && ! $cita->yaPaso(), 422);

        $cita->cambiarEstado(Cita::CONFIRMADA, $request->user(), 'Confirmada por el establecimiento');

        return back()->with('ok', 'Cita '.$cita->codigo.' confirmada.');
    }

    public function cancelar(Request $request, Cita $cita): RedirectResponse
    {
        $this->authorize('gestionar', $cita);
        abort_unless($cita->esActiva(), 422);

        $datos = $request->validate(['motivo' => ['required', 'string', 'max:300']], [
            'motivo.required' => 'Indica el motivo; el paciente lo verá en su historial.',
        ]);

        $cita->motivo_cancelacion = $datos['motivo'];
        $cita->cambiarEstado(Cita::CANCELADA, $request->user(), 'Cancelada por el establecimiento: '.$datos['motivo']);

        return back()->with('ok', 'Cita '.$cita->codigo.' cancelada.');
    }

    public function asistencia(Request $request, Cita $cita): RedirectResponse
    {
        $this->authorize('gestionar', $cita);
        abort_unless($cita->admiteAsistencia(), 422, 'La asistencia se registra cuando la cita ya empezó.');

        $datos = $request->validate([
            'asistio' => ['required', 'boolean'],
            'nota_profesional' => ['nullable', 'string', 'max:1000'],
        ]);

        if (isset($datos['nota_profesional'])) {
            $cita->nota_profesional = $datos['nota_profesional'];
        }

        $nuevo = $datos['asistio'] ? Cita::ATENDIDA : Cita::NO_ASISTIO;
        $cita->cambiarEstado($nuevo, $request->user(), $datos['asistio'] ? 'Asistencia registrada' : 'Inasistencia registrada');

        return back()->with('ok', $datos['asistio'] ? 'Registraste la asistencia.' : 'Registraste la inasistencia.');
    }

    public function nota(Request $request, Cita $cita): RedirectResponse
    {
        $this->authorize('gestionar', $cita);

        $cita->update($request->validate(['nota_profesional' => ['nullable', 'string', 'max:1000']]));

        return back()->with('ok', 'Nota guardada.');
    }

    /** Historial de un paciente dentro del alcance de quien consulta. */
    public function paciente(Request $request, User $paciente): View
    {
        $user = $request->user();

        $citas = $this->alcance($user)
            ->where('paciente_id', $paciente->id)
            ->with(['servicio', 'profesional.user', 'establecimiento'])
            ->orderByDesc('fecha')->orderByDesc('hora_inicio')
            ->get();

        abort_if($citas->isEmpty(), 403, 'Este paciente no tiene citas con tu establecimiento.');

        return view('gestion.paciente', [
            'paciente' => $paciente,
            'citas' => $citas,
            'resumen' => $this->resumenPaciente($paciente, $user),
        ]);
    }

    /** Citas que el usuario puede ver y gestionar. */
    private function alcance(User $user): Builder
    {
        return Cita::query()
            ->when($user->esProfesional(), fn ($q) => $q->where('profesional_id', $user->profesional?->id ?? 0))
            ->when($user->esAdmin() && ! $user->esAdminGeneral(), fn ($q) => $q->where('establecimiento_id', $user->establecimiento_id));
    }

    private function profesionalesVisibles(User $user): Builder
    {
        return Profesional::query()
            ->when($user->esProfesional(), fn ($q) => $q->whereKey($user->profesional?->id ?? 0))
            ->when($user->esAdmin() && ! $user->esAdminGeneral(), fn ($q) => $q->where('establecimiento_id', $user->establecimiento_id));
    }

    /** @return array{atendidas: int, inasistencias: int, canceladas: int, tasa: ?float} */
    private function resumenPaciente(User $paciente, User $consulta): array
    {
        $conteo = $this->alcance($consulta)
            ->where('paciente_id', $paciente->id)
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $atendidas = (int) ($conteo[Cita::ATENDIDA] ?? 0);
        $faltas = (int) ($conteo[Cita::NO_ASISTIO] ?? 0);
        $base = $atendidas + $faltas;

        return [
            'atendidas' => $atendidas,
            'inasistencias' => $faltas,
            'canceladas' => (int) ($conteo[Cita::CANCELADA] ?? 0),
            'tasa' => $base ? round($faltas * 100 / $base, 1) : null,
        ];
    }
}
