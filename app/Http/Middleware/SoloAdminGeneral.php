<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SoloAdminGeneral
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->esAdminGeneral(), 403, 'Solo el administrador general gestiona usuarios.');

        return $next($request);
    }
}
