<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Bloqueo;
use App\Models\Cita;
use App\Models\Horario;
use App\Models\Profesional;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Configuración de horarios de atención y bloqueos de agenda de un profesional.
 */
class HorarioController extends Controller
{
    /** Atajo: el profesional entra directo a sus propios horarios. */
    public function propios(Request $request): RedirectResponse
    {
        $profesional = $request->user()->profesional;
        abort_unless($profesional, 404);

        return redirect()->route('gestion.horarios', $profesional);
    }

    public function index(Profesional $profesional): View
    {
        $this->authorize('gestionar', $profesional);

        $profesional->load(['user', 'establecimiento', 'horarios', 'servicios']);

        return view('gestion.horarios', [
            'p' => $profesional,
            'porDia' => $profesional->horarios->groupBy('dia_semana'),
            'bloqueos' => $profesional->bloqueos()->where('fin', '>=', now())->get(),
        ]);
    }

    public function store(Request $request, Profesional $profesional): RedirectResponse
    {
        $this->authorize('gestionar', $profesional);

        $datos = $request->validate([
            'dias' => ['required', 'array', 'min:1'],
            'dias.*' => ['integer', 'between:1,7'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ], [
            'dias.required' => 'Elige al menos un día.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la de inicio.',
        ]);

        $inicio = $datos['hora_inicio'].':00';
        $fin = $datos['hora_fin'].':00';

        foreach ($datos['dias'] as $dia) {
            $choca = $profesional->horarios()
                ->where('dia_semana', $dia)
                ->where('hora_inicio', '<', $fin)
                ->where('hora_fin', '>', $inicio)
                ->exists();

            if ($choca) {
                throw ValidationException::withMessages([
                    'hora_inicio' => 'El '.mb_strtolower(Horario::DIAS[$dia]).' ya tiene un horario que se cruza con '.$datos['hora_inicio'].'–'.$datos['hora_fin'].'.',
                ]);
            }
        }

        foreach ($datos['dias'] as $dia) {
            $profesional->horarios()->create(['dia_semana' => $dia, 'hora_inicio' => $inicio, 'hora_fin' => $fin]);
        }

        return back()->with('ok', 'Horario agregado.');
    }

    public function destroy(Profesional $profesional, Horario $horario): RedirectResponse
    {
        $this->authorize('gestionar', $profesional);
        abort_unless($horario->profesional_id === $profesional->id, 404);

        $futuras = Cita::activas()
            ->where('profesional_id', $profesional->id)
            ->where('fecha', '>=', now()->toDateString())
            ->where('hora_inicio', '>=', $horario->hora_inicio)
            ->where('hora_fin', '<=', $horario->hora_fin)
            ->get()
            ->filter(fn ($c) => $c->fecha->isoWeekday() === (int) $horario->dia_semana)
            ->count();

        $horario->delete();

        $mensaje = 'Horario eliminado.';
        if ($futuras) {
            $mensaje .= ' Ojo: hay '.$futuras.' cita(s) ya reservadas en ese rango; siguen vigentes hasta que las canceles o reprogrames.';
        }

        return back()->with('ok', $mensaje);
    }

    public function storeBloqueo(Request $request, Profesional $profesional): RedirectResponse
    {
        $this->authorize('gestionar', $profesional);

        $datos = $request->validate([
            'desde_fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'desde_hora' => ['required', 'date_format:H:i'],
            'hasta_fecha' => ['required', 'date_format:Y-m-d', 'after_or_equal:desde_fecha'],
            'hasta_hora' => ['required', 'date_format:H:i'],
            'motivo' => ['nullable', 'string', 'max:150'],
        ]);

        $inicio = Carbon::parse($datos['desde_fecha'].' '.$datos['desde_hora']);
        $fin = Carbon::parse($datos['hasta_fecha'].' '.$datos['hasta_hora']);

        if ($fin->lte($inicio)) {
            throw ValidationException::withMessages(['hasta_hora' => 'El fin del bloqueo debe ser posterior al inicio.']);
        }

        Bloqueo::create([
            'profesional_id' => $profesional->id,
            'inicio' => $inicio,
            'fin' => $fin,
            'motivo' => $datos['motivo'] ?? null,
        ]);

        $afectadas = Cita::activas()
            ->where('profesional_id', $profesional->id)
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->get()
            ->filter(fn ($c) => $c->inicio->lt($fin) && Carbon::parse($c->fecha->toDateString().' '.$c->hora_fin)->gt($inicio))
            ->count();

        $mensaje = 'Bloqueo registrado. Esos horarios ya no se ofrecen a los pacientes.';
        if ($afectadas) {
            $mensaje .= ' Hay '.$afectadas.' cita(s) reservadas en ese periodo: revísalas en la agenda.';
        }

        return back()->with('ok', $mensaje);
    }

    public function destroyBloqueo(Profesional $profesional, Bloqueo $bloqueo): RedirectResponse
    {
        $this->authorize('gestionar', $profesional);
        abort_unless($bloqueo->profesional_id === $profesional->id, 404);

        $bloqueo->delete();

        return back()->with('ok', 'Bloqueo eliminado.');
    }
}
