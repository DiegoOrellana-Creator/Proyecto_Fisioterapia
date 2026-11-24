<?php

namespace App\Policies;

use App\Models\Profesional;
use App\Models\User;

class ProfesionalPolicy
{
    /** Administrar horarios, bloqueos y perfil de un profesional. */
    public function gestionar(User $user, Profesional $profesional): bool
    {
        if ($user->esProfesional()) {
            return $user->profesional?->id === $profesional->id;
        }

        if ($user->esAdmin()) {
            return $user->esAdminGeneral() || $user->establecimiento_id === $profesional->establecimiento_id;
        }

        return false;
    }
}
