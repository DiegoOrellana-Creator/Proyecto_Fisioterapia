<?php

namespace App\Policies;

use App\Models\Cita;
use App\Models\User;

class CitaPolicy
{
    /** Ver el detalle de una cita. */
    public function view(User $user, Cita $cita): bool
    {
        return $cita->paciente_id === $user->id || $this->gestionar($user, $cita);
    }

    /** Cancelar, reprogramar o valorar como paciente. */
    public function paciente(User $user, Cita $cita): bool
    {
        return $user->esPaciente() && $cita->paciente_id === $user->id;
    }

    /** Confirmar, registrar asistencia, cancelar desde el establecimiento. */
    public function gestionar(User $user, Cita $cita): bool
    {
        if ($user->esProfesional()) {
            return $user->profesional?->id === $cita->profesional_id;
        }

        if ($user->esAdmin()) {
            return $user->esAdminGeneral() || $user->establecimiento_id === $cita->establecimiento_id;
        }

        return false;
    }
}
