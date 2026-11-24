<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Registra y reprograma reservas. Cada operación bloquea la fila del profesional
 * dentro de una transacción, de modo que dos pacientes no puedan quedarse con el
 * mismo horario aunque reserven al mismo tiempo.
 */
class Reservas
{
    public function __construct(private Disponibilidad $disponibilidad) {}

    /**
     * Reserva una o varias sesiones semanales consecutivas a la misma hora.
     *
     * @return Collection<int, Cita>
     */
    public function reservar(User $paciente, Profesional $profesional, Servicio $servicio, string $fecha, string $hora, int $sesiones = 1, ?string $nota = null): Collection
    {
        $sesiones = max(1, min($sesiones, 12));

        return DB::transaction(function () use ($paciente, $profesional, $servicio, $fecha, $hora, $sesiones, $nota) {
            Profesional::whereKey($profesional->id)->lockForUpdate()->first();

            $grupo = $sesiones > 1 ? strtoupper(Str::random(8)) : null;
            $citas = collect();

            for ($i = 0; $i < $sesiones; $i++) {
                $inicio = Carbon::parse($fecha.' '.$hora)->addWeeks($i);
                $fin = $inicio->copy()->addMinutes((int) $servicio->duracion_min);

                if ($error = $this->disponibilidad->validarEspacio($profesional, $servicio, $inicio)) {
                    throw ValidationException::withMessages([
                        'hora' => $sesiones > 1
                            ? 'Sesión '.($i + 1).' ('.$inicio->translatedFormat('l j \d\e F').'): '.$error
                            : $error,
                    ]);
                }

                if ($this->pacienteOcupado($paciente, $inicio, $fin)) {
                    throw ValidationException::withMessages([
                        'hora' => 'Ya tienes otra cita el '.$inicio->translatedFormat('j \d\e F').' a esa hora.',
                    ]);
                }

                $cita = Cita::create([
                    'codigo' => $this->nuevoCodigo(),
                    'paciente_id' => $paciente->id,
                    'profesional_id' => $profesional->id,
                    'servicio_id' => $servicio->id,
                    'establecimiento_id' => $profesional->establecimiento_id,
                    'fecha' => $inicio->toDateString(),
                    'hora_inicio' => $inicio->format('H:i:s'),
                    'hora_fin' => $fin->format('H:i:s'),
                    'estado' => Cita::SOLICITADA,
                    'precio' => $servicio->precio,
                    'numero_sesion' => $i + 1,
                    'total_sesiones' => $sesiones,
                    'grupo' => $grupo,
                    'nota_paciente' => $nota,
                ]);

                $cita->historial()->create([
                    'user_id' => $paciente->id,
                    'estado_anterior' => null,
                    'estado_nuevo' => Cita::SOLICITADA,
                    'detalle' => 'Reserva registrada por el paciente',
                ]);

                $citas->push($cita);
            }

            return $citas;
        });
    }

    public function reprogramar(Cita $cita, string $fecha, string $hora, User $por): Cita
    {
        return DB::transaction(function () use ($cita, $fecha, $hora, $por) {
            Profesional::whereKey($cita->profesional_id)->lockForUpdate()->first();

            $inicio = Carbon::parse($fecha.' '.$hora);
            $fin = $inicio->copy()->addMinutes((int) $cita->servicio->duracion_min);

            if ($error = $this->disponibilidad->validarEspacio($cita->profesional, $cita->servicio, $inicio, $cita->id)) {
                throw ValidationException::withMessages(['hora' => $error]);
            }

            if ($this->pacienteOcupado($cita->paciente, $inicio, $fin, $cita->id)) {
                throw ValidationException::withMessages(['hora' => 'Ya tienes otra cita a esa hora.']);
            }

            $anterior = $cita->fecha->format('d/m/Y').' '.substr($cita->hora_inicio, 0, 5);

            $cita->fill([
                'fecha' => $inicio->toDateString(),
                'hora_inicio' => $inicio->format('H:i:s'),
                'hora_fin' => $fin->format('H:i:s'),
            ]);
            $cita->cambiarEstado(
                Cita::REPROGRAMADA,
                $por,
                'De '.$anterior.' a '.$inicio->format('d/m/Y H:i')
            );

            return $cita;
        });
    }

    private function pacienteOcupado(User $paciente, Carbon $inicio, Carbon $fin, ?int $ignorar = null): bool
    {
        return Cita::activas()
            ->where('paciente_id', $paciente->id)
            ->whereDate('fecha', $inicio->toDateString())
            ->when($ignorar, fn ($q) => $q->where('id', '!=', $ignorar))
            ->where('hora_inicio', '<', $fin->format('H:i:s'))
            ->where('hora_fin', '>', $inicio->format('H:i:s'))
            ->exists();
    }

    private function nuevoCodigo(): string
    {
        do {
            $codigo = 'FA-'.strtoupper(Str::random(6));
        } while (Cita::where('codigo', $codigo)->exists());

        return $codigo;
    }
}
