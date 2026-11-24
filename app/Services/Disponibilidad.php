<?php

namespace App\Services;

use App\Models\Bloqueo;
use App\Models\Cita;
use App\Models\Profesional;
use App\Models\Servicio;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Calcula los espacios libres de un profesional a partir de su horario de atención,
 * las citas activas y los bloqueos (vacaciones, descansos, ausencias).
 */
class Disponibilidad
{
    /** Cada cuántos minutos se ofrece un inicio de cita. */
    public const PASO_MIN = 30;

    /** Anticipación mínima para reservar el mismo día. */
    public const ANTICIPACION_MIN = 60;

    /** Hasta cuántos días adelante se puede reservar. */
    public const DIAS_MAXIMOS = 60;

    /**
     * @return array<int, array{inicio: string, fin: string}>
     */
    public function horariosLibres(Profesional $profesional, Servicio $servicio, CarbonInterface $fecha, ?int $ignorarCitaId = null): array
    {
        $fecha = Carbon::parse($fecha)->startOfDay();
        $duracion = (int) $servicio->duracion_min;

        $hoy = now()->startOfDay();
        if ($fecha->lt($hoy) || $fecha->gt($hoy->copy()->addDays(self::DIAS_MAXIMOS))) {
            return [];
        }

        $rangos = $profesional->horarios()->where('dia_semana', $fecha->isoWeekday())->get();
        if ($rangos->isEmpty()) {
            return [];
        }

        $ocupados = $this->ocupados($profesional, $fecha, $ignorarCitaId);
        $limite = now()->addMinutes(self::ANTICIPACION_MIN);

        $libres = [];
        foreach ($rangos as $rango) {
            $inicio = $this->en($fecha, $rango->hora_inicio);
            $finRango = $this->en($fecha, $rango->hora_fin);

            for ($t = $inicio->copy(); $t->copy()->addMinutes($duracion)->lte($finRango); $t->addMinutes(self::PASO_MIN)) {
                $fin = $t->copy()->addMinutes($duracion);

                if ($t->lt($limite)) {
                    continue;
                }
                if ($this->chocaCon($ocupados, $t, $fin)) {
                    continue;
                }

                $libres[] = ['inicio' => $t->format('H:i'), 'fin' => $fin->format('H:i')];
            }
        }

        return $libres;
    }

    /**
     * Verifica que un espacio concreto pueda reservarse. Devuelve null si es válido
     * o un mensaje explicando por qué no lo es.
     */
    public function validarEspacio(Profesional $profesional, Servicio $servicio, CarbonInterface $inicio, ?int $ignorarCitaId = null): ?string
    {
        $inicio = Carbon::parse($inicio);
        $fin = $inicio->copy()->addMinutes((int) $servicio->duracion_min);

        if ($inicio->lt(now()->addMinutes(self::ANTICIPACION_MIN))) {
            return 'Ese horario ya pasó o está demasiado cerca. Elige otro con al menos una hora de anticipación.';
        }
        if ($inicio->gt(now()->addDays(self::DIAS_MAXIMOS)->endOfDay())) {
            return 'Solo se puede reservar hasta '.self::DIAS_MAXIMOS.' días adelante.';
        }

        $dentroDelHorario = $profesional->horarios()
            ->where('dia_semana', $inicio->isoWeekday())
            ->get()
            ->contains(fn ($h) => $inicio->gte($this->en($inicio, $h->hora_inicio)) && $fin->lte($this->en($inicio, $h->hora_fin)));

        if (! $dentroDelHorario) {
            return 'El profesional no atiende en ese horario.';
        }

        if ($this->chocaCon($this->ocupados($profesional, $inicio->copy()->startOfDay(), $ignorarCitaId), $inicio, $fin)) {
            return 'Ese horario acaba de ser ocupado. Elige otro.';
        }

        return null;
    }

    /**
     * Próximos espacios libres de varios profesionales, para mostrar en la portada.
     *
     * @return Collection<int, array>
     */
    public function proximosLibres(int $cantidad = 6): Collection
    {
        $profesionales = Profesional::activos()
            ->whereHas('establecimiento', fn ($q) => $q->where('activo', true))
            ->with(['user', 'establecimiento', 'servicios' => fn ($q) => $q->activos()->orderBy('duracion_min')])
            ->get();

        $resultado = collect();

        foreach ($profesionales as $profesional) {
            $servicio = $profesional->servicios->first();
            if (! $servicio) {
                continue;
            }

            for ($d = 0; $d < 7; $d++) {
                $fecha = now()->startOfDay()->addDays($d);
                $libres = $this->horariosLibres($profesional, $servicio, $fecha);
                if ($libres) {
                    $resultado->push([
                        'profesional' => $profesional,
                        'servicio' => $servicio,
                        'fecha' => $fecha,
                        'hora' => $libres[0]['inicio'],
                        'cuando' => $this->en($fecha, $libres[0]['inicio']),
                    ]);
                    break;
                }
            }
        }

        return $resultado->sortBy('cuando')->take($cantidad)->values();
    }

    /**
     * Primer horario libre de cualquier profesional del establecimiento en los próximos días.
     * Sirve para el estado de disponibilidad del catálogo.
     */
    public function proximoLibreEstablecimiento(\App\Models\Establecimiento $establecimiento, int $dias = 7): ?Carbon
    {
        $profesionales = $establecimiento->profesionales()
            ->where('activo', true)
            ->with(['servicios' => fn ($q) => $q->activos()->orderBy('duracion_min')])
            ->get();

        for ($d = 0; $d < $dias; $d++) {
            $fecha = now()->startOfDay()->addDays($d);
            $mejor = null;
            foreach ($profesionales as $p) {
                $servicio = $p->servicios->first();
                if (! $servicio) {
                    continue;
                }
                $libres = $this->horariosLibres($p, $servicio, $fecha);
                if ($libres) {
                    $cuando = $this->en($fecha, $libres[0]['inicio']);
                    $mejor = $mejor && $mejor->lte($cuando) ? $mejor : $cuando;
                }
            }
            if ($mejor) {
                return $mejor;
            }
        }

        return null;
    }

    /**
     * Intervalos ocupados del día (citas activas y bloqueos).
     *
     * @return array<int, array{0: Carbon, 1: Carbon}>
     */
    private function ocupados(Profesional $profesional, Carbon $dia, ?int $ignorarCitaId): array
    {
        $intervalos = [];

        Cita::activas()
            ->where('profesional_id', $profesional->id)
            ->whereDate('fecha', $dia->toDateString())
            ->when($ignorarCitaId, fn ($q) => $q->where('id', '!=', $ignorarCitaId))
            ->get(['hora_inicio', 'hora_fin'])
            ->each(function ($c) use (&$intervalos, $dia) {
                $intervalos[] = [$this->en($dia, $c->hora_inicio), $this->en($dia, $c->hora_fin)];
            });

        Bloqueo::where('profesional_id', $profesional->id)
            ->where('inicio', '<', $dia->copy()->endOfDay())
            ->where('fin', '>', $dia->copy()->startOfDay())
            ->get(['inicio', 'fin'])
            ->each(function ($b) use (&$intervalos) {
                $intervalos[] = [$b->inicio, $b->fin];
            });

        return $intervalos;
    }

    private function chocaCon(array $ocupados, Carbon $inicio, Carbon $fin): bool
    {
        foreach ($ocupados as [$oInicio, $oFin]) {
            if ($inicio->lt($oFin) && $fin->gt($oInicio)) {
                return true;
            }
        }

        return false;
    }

    private function en(CarbonInterface $dia, string $hora): Carbon
    {
        return Carbon::parse(Carbon::parse($dia)->toDateString().' '.substr($hora, 0, 5));
    }
}
