<?php

namespace App\Http\Controllers;

use App\Models\Profesional;
use App\Models\Servicio;
use App\Services\Disponibilidad;
use App\Services\Reservas;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReservaController extends Controller
{
    /** Paso de reserva: elegir servicio, día, hora y número de sesiones. */
    public function create(Request $request, Profesional $profesional): View
    {
        abort_unless($profesional->activo && $profesional->establecimiento->activo, 404);

        $profesional->load(['user', 'establecimiento', 'horarios', 'servicios' => fn ($s) => $s->activos()->orderBy('precio')]);

        $servicio = $profesional->servicios->firstWhere('id', (int) $request->query('servicio'))
            ?? $profesional->servicios->first();

        return view('reserva.create', [
            'p' => $profesional,
            'servicioInicial' => $servicio,
            'fechaInicial' => $request->query('fecha'),
            'horaInicial' => $request->query('hora'),
            'diasAtencion' => $profesional->horarios->pluck('dia_semana')->unique()->values(),
            'maxDias' => Disponibilidad::DIAS_MAXIMOS,
        ]);
    }

    /** Horarios libres de un día (JSON, usado por el formulario). */
    public function disponibilidad(Request $request, Profesional $profesional, Disponibilidad $disponibilidad): JsonResponse
    {
        $datos = $request->validate([
            'servicio' => ['required', 'integer'],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'ignorar' => ['nullable', 'integer'],
        ]);

        $servicio = $profesional->servicios()->activos()->findOrFail($datos['servicio']);

        $ignorar = null;
        if (! empty($datos['ignorar']) && $request->user()) {
            // Al reprogramar, el horario actual de la propia cita cuenta como libre
            $ignorar = $request->user()->citas()->whereKey($datos['ignorar'])->value('id');
        }

        return response()->json([
            'fecha' => $datos['fecha'],
            'horarios' => $disponibilidad->horariosLibres($profesional, $servicio, Carbon::parse($datos['fecha']), $ignorar),
        ]);
    }

    public function store(Request $request, Profesional $profesional, Reservas $reservas): RedirectResponse
    {
        abort_unless($request->user()->esPaciente(), 403, 'Solo los pacientes pueden reservar citas.');

        $datos = $request->validate([
            'servicio_id' => ['required', 'integer', Rule::exists('profesional_servicio', 'servicio_id')->where('profesional_id', $profesional->id)],
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i'],
            'sesiones' => ['required', 'integer', 'min:1', 'max:12'],
            'nota' => ['nullable', 'string', 'max:500'],
        ], [
            'hora.required' => 'Elige un horario disponible.',
        ]);

        $servicio = Servicio::activos()->findOrFail($datos['servicio_id']);

        $citas = $reservas->reservar(
            $request->user(), $profesional, $servicio,
            $datos['fecha'], $datos['hora'], (int) $datos['sesiones'], $datos['nota'] ?? null
        );

        $mensaje = $citas->count() > 1
            ? 'Reservaste '.$citas->count().' sesiones. El establecimiento debe confirmarlas.'
            : 'Reserva enviada. El establecimiento debe confirmarla.';

        return redirect()->route('paciente.citas.show', $citas->first())->with('ok', $mensaje);
    }
}
