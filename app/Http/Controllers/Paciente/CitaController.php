<?php

namespace App\Http\Controllers\Paciente;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Valoracion;
use App\Services\Disponibilidad;
use App\Services\Reservas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CitaController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $base = fn () => $user->citas()->with(['profesional.user', 'servicio', 'establecimiento', 'valoracion']);

        $proximas = $base()->activas()
            ->where('fecha', '>=', now()->toDateString())
            ->orderBy('fecha')->orderBy('hora_inicio')->get()
            ->reject(fn ($c) => $c->yaPaso());

        $historial = $base()
            ->whereNotIn('id', $proximas->pluck('id'))
            ->orderByDesc('fecha')->orderByDesc('hora_inicio')
            ->paginate(10);

        $porValorar = $base()
            ->where('estado', Cita::ATENDIDA)
            ->whereDoesntHave('valoracion')
            ->orderByDesc('fecha')
            ->get();

        $resumen = $user->citas()->selectRaw('estado, COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado');

        return view('paciente.citas', compact('proximas', 'historial', 'resumen', 'porValorar'));
    }

    public function show(Cita $cita): View
    {
        $this->authorize('paciente', $cita);

        $cita->load(['profesional.user', 'servicio', 'establecimiento', 'historial.user', 'valoracion']);

        $serie = $cita->grupo
            ? Cita::where('grupo', $cita->grupo)->orderBy('numero_sesion')->get()
            : collect();

        return view('paciente.cita', compact('cita', 'serie'));
    }

    public function cancelar(Request $request, Cita $cita): RedirectResponse
    {
        $this->authorize('paciente', $cita);
        abort_unless($cita->modificablePorPaciente(), 422, 'Esta cita ya no se puede cancelar.');

        $datos = $request->validate(['motivo' => ['required', 'string', 'max:300']], [
            'motivo.required' => 'Cuéntanos brevemente por qué cancelas; ayuda al establecimiento a reorganizar la agenda.',
        ]);

        $cita->motivo_cancelacion = $datos['motivo'];
        $cita->cambiarEstado(Cita::CANCELADA, $request->user(), 'Cancelada por el paciente: '.$datos['motivo']);

        return redirect()->route('paciente.citas.show', $cita)->with('ok', 'Cancelaste la cita. El horario quedó libre para otro paciente.');
    }

    public function reprogramarForm(Cita $cita): View
    {
        $this->authorize('paciente', $cita);
        abort_unless($cita->modificablePorPaciente(), 422, 'Esta cita ya no se puede reprogramar.');

        $cita->load(['profesional.user', 'profesional.horarios', 'servicio', 'establecimiento']);

        return view('paciente.reprogramar', [
            'cita' => $cita,
            'diasAtencion' => $cita->profesional->horarios->pluck('dia_semana')->unique()->values(),
            'maxDias' => Disponibilidad::DIAS_MAXIMOS,
        ]);
    }

    public function reprogramar(Request $request, Cita $cita, Reservas $reservas): RedirectResponse
    {
        $this->authorize('paciente', $cita);
        abort_unless($cita->modificablePorPaciente(), 422, 'Esta cita ya no se puede reprogramar.');

        $datos = $request->validate([
            'fecha' => ['required', 'date_format:Y-m-d'],
            'hora' => ['required', 'date_format:H:i'],
        ], ['hora.required' => 'Elige el nuevo horario.']);

        $reservas->reprogramar($cita, $datos['fecha'], $datos['hora'], $request->user());

        return redirect()->route('paciente.citas.show', $cita)->with('ok', 'Cita reprogramada. El establecimiento debe confirmar el nuevo horario.');
    }

    public function valorar(Request $request, Cita $cita): RedirectResponse
    {
        $this->authorize('paciente', $cita);
        abort_unless($cita->puedeValorarse(), 422, 'Solo puedes valorar una vez cada cita atendida.');

        $datos = $request->validate([
            'puntuacion' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['nullable', 'string', 'max:600'],
        ], ['puntuacion.required' => 'Elige de 1 a 5 estrellas.']);

        Valoracion::create($datos + [
            'cita_id' => $cita->id,
            'paciente_id' => $cita->paciente_id,
            'profesional_id' => $cita->profesional_id,
            'establecimiento_id' => $cita->establecimiento_id,
        ]);

        return redirect()->route('paciente.citas.show', $cita)->with('ok', 'Gracias. Tu valoración ya aparece en el perfil del profesional.');
    }
}
